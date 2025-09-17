<?php

namespace MetaFox\App\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use MetaFox\App\Http\Requests\v1\Package\Admin\ImportRequest;
use MetaFox\App\Http\Requests\v1\Package\Admin\StoreRequest;
use MetaFox\App\Http\Requests\v1\Package\Admin\UpdateRequest;
use MetaFox\App\Http\Resources\v1\Package\Admin\EditPackageForm;
use MetaFox\App\Http\Resources\v1\Package\Admin\ImportPackageForm;
use MetaFox\App\Http\Resources\v1\Package\Admin\MakePackageForm;
use MetaFox\App\Http\Resources\v1\Package\Admin\PackageDetail as Detail;
use MetaFox\App\Http\Resources\v1\Package\Admin\PackageItem;
use MetaFox\App\Http\Resources\v1\Package\Admin\PackageItemCollection;
use MetaFox\App\Http\Resources\v1\Package\Admin\PurchasedPackageItemCollection;
use MetaFox\App\Models\Package;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\App\Support\Browse\Scopes\Package\SortScope;
use MetaFox\App\Support\Browse\Scopes\Package\TypeScope;
use MetaFox\App\Support\MetaFoxStore;
use MetaFox\App\Support\PackageExporter;
use MetaFox\App\Support\PackageInstaller;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\PackageAdminController::$controllers.
 */

/**
 * Class PackageAdminController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @group admin/module
 * @ignore
 * @authenticated
 * @admincp
 */
class PackageAdminController extends ApiController
{
    /**
     * @var PackageRepositoryInterface
     */
    private PackageRepositoryInterface $repository;

    /**
     * @var MetaFoxStore
     */
    private MetaFoxStore $store;

    /**
     * PackageAdminController constructor.
     *
     * @param PackageRepositoryInterface $repository
     */
    public function __construct(PackageRepositoryInterface $repository)
    {
        $this->repository = $repository;

        $this->store = resolve(MetaFoxStore::class);
    }

    /**
     * Browse installed modules.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $keyword          = $request->get('q');
        $status           = $request->get('name');
        $type             = $request->get('type');
        $isActive         = $request->get('is_active');
        $isCore           = $request->get('is_core');
        $updateAvailable  = $request->get('update_available');
        $isCheckingUpdate = $request->get('is_checking_update');

        if ($isCheckingUpdate) {
            $this->store->purchased();
        }

        $sortScope = new SortScope();
        $sortScope->setSort($request->get('sort', 'title'))
            ->setSortType($request->get('sort_type', 'asc'));

        $query = $this->repository->getModel()->newQuery()
            ->whereNot('category', 'platform')
            ->orWhereNull('category');

        switch ($status) {
            case 'uploaded':
                $query->where('is_installed', 0);
                break;
            default:
                $query->where('is_installed', 1);
        }

        if ($keyword) {
            $query = $query->addScope(new SearchScope($keyword, ['title', 'author']));
        }

        if ($type) {
            $query->addScope(new TypeScope($type));
        }

        if (null !== $isActive) {
            $query->where('is_active', '=', $isActive);
        }

        if (null !== $isCore) {
            $query->where('is_core', '=', $isCore);
        }

        if (null !== $updateAvailable) {
            match ((int) $updateAvailable) {
                1       => $query->whereRaw('latest_version > version'),
                default => $query->whereRaw('latest_version <= version'),
            };
        }

        $query->addScope($sortScope);

        return $this->success(new PackageItemCollection($query->cursor()));
    }

    public function create(): JsonResponse
    {
        return $this->success(new MakePackageForm());
    }

    /**
     * Browse uploaded modules.
     *
     * @return JsonResponse
     */
    public function uploaded(): JsonResponse
    {
        return $this->success(PackageItem::collection([]));
    }

    public function purchased(): JsonResponse
    {
        $data = $this->store->purchased();

        return $this->success(new PurchasedPackageItemCollection($data));
    }

    /**
     * View module.
     *
     * @param mixed $package
     * @return JsonResponse
     */
    public function show(mixed $package): JsonResponse
    {
        $resource = $this->repository->find($package);

        return $this->success(new Detail($resource));
    }

    /**
     * Export a module.
     *
     * @param int $id
     *
     * @return BinaryFileResponse
     */
    public function export(int $id): BinaryFileResponse
    {
        $package = $this->repository->find($id);
        $channel = config('app.mfox_app_channel');

        $named = sprintf('%s-%s.zip', preg_replace('/(\W+)/m', '-', $package->name), $package->version);

        $resource = resolve(PackageExporter::class)->export($package->name, false, $channel);

        $headers = ['Access-Control-Expose-Headers' => 'Content-Disposition'];

        return response()->download($resource, $named, $headers)->deleteFileAfterSend(true);
    }

    /**
     * Update active status.
     * @param ActiveRequest $request
     * @param int           $id
     * @return JsonResponse
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $package = $this->repository->find($id);

        $active = $params['active'] ? 1 : 0;

        $package->is_active = $active;

        $package->save();

        Log::channel('dev')->info(sprintf('active= %d package= %d', $active, $id));

        $package->refresh();

        match ((bool) $package->is_active) {
            true  => app('events')->dispatch('packages.activated', [$package->name]),
            false => app('events')->dispatch('packages.deactivated', [$package->name]),
        };

        Artisan::call('optimize:clear');

        $message = match ($active) {
            1       => __p('app::phrase.package_actived_successfully'),
            default => __p('app::phrase.package_inactived_successfully'),
        };

        return $this->success(new Detail($package), [], $message);
    }

    public function formImport(): JsonResponse
    {
        return $this->success(new ImportPackageForm());
    }

    /**
     * View creation form.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        $package = $this->repository->find($id);

        return $this->success(new EditPackageForm($package));
    }

    /**
     * Delete module.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group admin/module
     */
    public function uninstall(int $id): JsonResponse
    {
        /** @var Package $package */
        $package = Package::query()->findOrFail($id);

        if ($package->is_active) {
            throw new InvalidArgumentException(__p('app::phrase.failed_unistalling_an_active_app'));
        }

        Artisan::call('package:uninstall', [
            'package' => $package->name,
        ]);

        Artisan::call('optimize:clear');

        return $this->success([
            'id' => $id,
        ], [], __p('app::phrase.package_deleted_successfully'));
    }

    /**
     * Delete module.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group admin/module
     */
    public function install(int $id): JsonResponse
    {
        /** @var Package $package */
        $package = Package::query()->findOrFail($id);

        Artisan::call('package:install', [
            'package' => $package->name,
        ]);

        $output = Artisan::output();

        Log::channel('installation')->debug($output);

        return $this->success([
            'id' => $id,
        ], [], __p('app::phrase.package_deleted_successfully'));
    }

    /**
     * Delete module.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @group admin/module
     */
    public function destroy(int $id): JsonResponse
    {
        $package = $this->repository->find($id);

        if ($package->is_installed) {
            throw new InvalidArgumentException(__p('app::phrase.failed_delete_not_uninstall_app'));
        }

        Artisan::call('package:uninstall', [
            'package' => $package->name,
            '--clean' => true,
        ]);

        return $this->success([
            'id' => $id,
        ], [], __p('app::phrase.package_deleted_successfully'));
    }

    /**
     * Import module.
     *
     * @param ImportRequest $request
     *
     * @return JsonResponse
     * @group admin/module
     */
    public function import(ImportRequest $request): JsonResponse
    {
        // 1. Set whether a client disconnect should abort script execution
        // 2. Increase time
        ignore_user_abort(true);
        set_time_limit(60);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->file('file');

        $filename = $uploadedFile->getRealPath();

        resolve(PackageInstaller::class)->install($filename);

        Artisan::call('optimize:clear');

        return $this->success([], [], __p('app::phrase.package_imported_successfully'));
    }

    /**
     * Create a package.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @ignore
     * @group admin/module
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $packageName = $params['package'];

        Artisan::call('package:make', $params);

        Artisan::call('optimize:clear');

        Artisan::call('package:discover');

        $packageName = $this->repository->setupPackage($packageName);

        Artisan::call('optimize:clear');

        return $this->success(new Detail($packageName), [
            'alert' => [
                'title'   => sprintf('Created Package %s', $packageName),
                'message' => "To create skeleton for frontend.
1. Open Terminal.
2. Go to the root of frontend project.
3. Run command: yarn metafox create-app $packageName",
            ],
        ]);
    }

    /**
     * Update module.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @group admin/module
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $package = $this->repository->find($id);

        $package->update($params);

        $composerFile = implode(DIRECTORY_SEPARATOR, [base_path(), $package->path, 'composer.json']);

        $composer = json_decode(mf_get_contents($composerFile), true);

        $package->updateComposer($composer);

        file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        Artisan::call('optimize:clear');

        return $this->success(new Detail($package));
    }

    public function batchActive(Request $request): JsonResponse
    {
        $params = $request->all();
        $ids    = Arr::get($params, 'id', []);
        $active = (int) Arr::get($params, 'active', 1);

        $query = $this->repository->getModel()->newQuery()->whereIn('id', $ids)
            ->where('is_core', false);

        $query->each(function (Package $item) use ($active) {
            $item->is_active = $active;
            $item->save();
            $item->refresh();

            match ((bool) $item->is_active) {
                true  => app('events')->dispatch('packages.activated', [$item->name]),
                false => app('events')->dispatch('packages.deactivated', [$item->name]),
            };
        });

        Artisan::call('optimize:clear');

        $rebuildMessage = __p('core::phrase.please_rebuild_your_site');
        $message        = match ($active) {
            1       => sprintf('%s %s', __p('app::phrase.packages_actived_successfully'), strip_tags($rebuildMessage)),
            default => sprintf('%s %s', __p('app::phrase.packages_inactived_successfully'), strip_tags($rebuildMessage)),
        };
        return $this->success([], [], $message);
    }
}
