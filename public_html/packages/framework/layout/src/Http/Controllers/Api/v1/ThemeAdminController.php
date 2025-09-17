<?php

namespace MetaFox\Layout\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use MetaFox\Layout\Http\Requests\v1\Theme\Admin\IndexRequest;
use MetaFox\Layout\Http\Requests\v1\Theme\Admin\StoreRequest;
use MetaFox\Layout\Http\Requests\v1\Theme\Admin\UpdateRequest;
use MetaFox\Layout\Http\Resources\v1\Theme\Admin\CreateThemeForm;
use MetaFox\Layout\Http\Resources\v1\Theme\Admin\ThemeDetail as Detail;
use MetaFox\Layout\Http\Resources\v1\Theme\Admin\ThemeItemCollection as ItemCollection;
use MetaFox\Layout\Models\Theme;
use MetaFox\Layout\Repositories\ThemeRepositoryInterface;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Layout\Http\Controllers\Api\ThemeAdminController::$controllers;.
 */

/**
 * Class ThemeAdminController.
 * @codeCoverageIgnore
 * @ignore
 */
class ThemeAdminController extends ApiController
{
    /**
     * @var ThemeRepositoryInterface
     */
    private ThemeRepositoryInterface $repository;

    /**
     * ThemeAdminController Constructor.
     *
     * @param ThemeRepositoryInterface $repository
     */
    public function __construct(ThemeRepositoryInterface $repository)
    {
        $this->repository = $repository;
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
        $data   = $this->repository->getModel()->newQuery()->where([
            'resolution' => 'web',
        ])->paginate($params['limit'] ?? 100);

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
    public function store(StoreRequest $request)
    {
        $params = $request->validated();

        $themeId = $params['theme_id'];
        $vendor  = Str::studly($params['vendor']);
        $name    = Str::studly('theme-' . $themeId);
        $package = sprintf('%s/theme-%s', Str::lower($vendor), Str::lower($themeId));

        // package also install.
        Artisan::call('package:make', [
            'package'  => $package,
            '--title'  => $params['title'],
            '--vendor' => $vendor,
            '--name'   => $name,
            '--type'   => 'theme',
        ]);

        $data = $this->repository->findByField('theme_id', $themeId)->firstOrFail();

        $this->navigate('/layout/theme/browse');

        return $this->success(new Detail($data));
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->find($id);

        return new Detail($data);
    }

    public function create()
    {
        return new CreateThemeForm();
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

    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        /** @var Theme $variant */
        $variant            = $this->repository->find($id);
        $variant->is_active = $params['active'] ?? 1;
        $variant->save();

        return $this->success([
            'id'        => $id,
            'is_active' => (int) $params['active'],
        ], [], __p('core::phrase.already_saved_changes'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws PermissionDeniedException
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var Theme $theme */
        $theme = $this->repository->find($id);

        if ($theme->is_active) {
            throw new PermissionDeniedException('Could not delete active theme');
        }

        if ($theme->is_system) {
            throw new PermissionDeniedException('Could not delete active theme');
        }

        $theme->delete();

        return $this->success([
            'id' => $id,
        ]);
    }
}
