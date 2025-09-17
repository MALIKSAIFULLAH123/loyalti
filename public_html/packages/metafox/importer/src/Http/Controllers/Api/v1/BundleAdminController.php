<?php

namespace MetaFox\Importer\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use MetaFox\Importer\Http\Requests\v1\Bundle\Admin\IndexRequest;
use MetaFox\Importer\Http\Requests\v1\Bundle\Admin\StoreRequest;
use MetaFox\Importer\Http\Requests\v1\Bundle\Admin\UpdateRequest;
use MetaFox\Importer\Http\Resources\v1\Bundle\Admin\BundleDetail as Detail;
use MetaFox\Importer\Http\Resources\v1\Bundle\Admin\BundleItemCollection as ItemCollection;
use MetaFox\Importer\Http\Resources\v1\Bundle\Admin\CreateBundleForm;
use MetaFox\Importer\Jobs\RunBundle;
use MetaFox\Importer\Models\Bundle;
use MetaFox\Importer\Repositories\BundleRepositoryInterface;
use MetaFox\Importer\Repositories\EntryRepositoryInterface;
use MetaFox\Importer\Repositories\LogRepositoryInterface;
use MetaFox\Importer\Supports\Status;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Importer\Http\Controllers\Api\BundleAdminController::$controllers;.
 */

/**
 * Class BundleAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class BundleAdminController extends ApiController
{
    /**
     * @var BundleRepositoryInterface
     */
    private BundleRepositoryInterface $repository;
    private EntryRepositoryInterface $entryRepository;
    private LogRepositoryInterface $logRepository;

    /**
     * BundleAdminController Constructor.
     *
     * @param BundleRepositoryInterface $repository
     * @param EntryRepositoryInterface  $entryRepository
     * @param LogRepositoryInterface    $logRepository
     */
    public function __construct(
        BundleRepositoryInterface $repository,
        EntryRepositoryInterface $entryRepository,
        LogRepositoryInterface $logRepository
    ) {
        $this->repository      = $repository;
        $this->entryRepository = $entryRepository;
        $this->logRepository   = $logRepository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest $request
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $data = $this->repository->viewBundles($params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        // 1. Set whether a client disconnect should abort script execution
        // 2. Increase time
        ignore_user_abort(true);
        set_time_limit(0);
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $params['file'];
        $chatType     = $params['chat_type'];
        $keepItemId   = (bool) $params['keep_item_id'];

        $filename = $uploadedFile->getRealPath();

        $this->repository->importScheduleArchive($filename, $chatType, $keepItemId);

        $nextAction = ['type' => 'navigate', 'payload' => ['url' => '/importer/bundle/browse']];

        return $this->success([], ['nextAction' => $nextAction]);
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->find($id);

        return $this->success($data);
    }

    public function create()
    {
        return $this->success(new CreateBundleForm());
    }

    public function retry(int $id)
    {
        /** @var Bundle $data */
        $data = $this->repository->getModel()
            ->newQuery()->where('id', $id)->first();

        if (!$data) {
            return $this->error(__p('importer::phrase.bundle_is_not_found'));
        }

        $data->status = Status::initial;
        $data->saveQuietly();

        RunBundle::dispatchSync($data->id, true);

        $data->refresh();

        return $this->success(new Detail($data));
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return Detail
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): Detail
    {
        $params = $request->validated();
        $data   = $this->repository->update($params, $id);

        return new Detail($data);
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->success([
            'id' => $id,
        ]);
    }

    public function statistic()
    {
        $running = $this->repository->isLocking();

        $bundleStats = $this->repository->getModel()->newQuery()
            ->select(['status', DB::raw('count(*) as total')])
            ->groupBy(['status'])
            ->get()
            ->toArray();

        $totalErrors = $this->logRepository->getModel()
            ->newQuery()
            ->where('level', '>=', 400)
            ->count();

        return $this->success([
            'component' => 'core.block.html',
            'props'     => [
                'title'        => '',
                'disableNl2br' => true,
                'content'      => view(
                    'importer::bundle/statistic',
                    compact('running', 'bundleStats', 'totalErrors')
                )->render(),
            ],
        ]);
    }
}
