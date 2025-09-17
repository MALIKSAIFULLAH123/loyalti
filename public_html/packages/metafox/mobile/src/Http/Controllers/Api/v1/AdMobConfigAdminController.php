<?php

namespace MetaFox\Mobile\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Mobile\Http\Resources\v1\AdMobConfig\Admin\AdMobConfigItemCollection as ItemCollection;
use MetaFox\Mobile\Http\Resources\v1\AdMobConfig\Admin\AdMobConfigDetail as Detail;
use MetaFox\Mobile\Repositories\AdMobConfigAdminRepositoryInterface;
use MetaFox\Mobile\Http\Requests\v1\AdMobConfig\Admin\IndexRequest;
use MetaFox\Mobile\Http\Requests\v1\AdMobConfig\Admin\StoreRequest;
use MetaFox\Mobile\Http\Requests\v1\AdMobConfig\Admin\UpdateRequest;
use MetaFox\Mobile\Http\Resources\v1\AdMobConfig\Admin\EditAdMobConfigForm;
use MetaFox\Mobile\Http\Resources\v1\AdMobConfig\Admin\StoreAdMobConfigForm;
use MetaFox\Mobile\Repositories\AdMobPageAdminRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;

/**
 * Class AdMobConfigAdminController.
 * @codeCoverageIgnore
 * @group admin/admob
 * @authenticated
 * @admincp
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdMobConfigAdminController extends ApiController
{
    /**
     * @var AdMobConfigAdminRepositoryInterface
     */
    private AdMobConfigAdminRepositoryInterface $repository;

    /**
     * @var AdMobPageAdminRepositoryInterface
     */
    private AdMobPageAdminRepositoryInterface $pageRepository;

    /**
     * AdMobConfigAdminController Constructor.
     *
     * @param AdMobConfigAdminRepositoryInterface $repository
     */
    public function __construct(
        AdMobConfigAdminRepositoryInterface $repository,
        AdMobPageAdminRepositoryInterface $pageRepository,
    ) {
        $this->repository     = $repository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Browse item.
     *
     * @param  IndexRequest   $request
     * @return ItemCollection
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->paginate($params['limit'] ?? 100);

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
        $user   = user();
        $params = $request->validated();
        $data   = $this->repository->createConfig($user, $params);

        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'     => '/mobile/admob/browse',
                    'replace' => true,
                ],
            ],
        ], __p('mobile::phrase.config_created_successfully'));
    }

    /**
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        if ($this->pageRepository->getAvailablePages()->count() == 0) {
            return $this->error(__p('mobile::phrase.no_page_available_for_add_config'));
        }

        $form = resolve(StoreAdMobConfigForm::class);

        return $this->success($form);
    }

    /**
     * @param  Request      $request
     * @param  int          $id
     * @return JsonResponse
     */
    public function edit(Request $request, int $id): JsonResponse
    {
        $form = resolve(EditAdMobConfigForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], array_merge($request->route()->parameters(), ['id' => $id]));
        }

        return $this->success($form);
    }

    /**
     * Update item.
     *
     * @param  UpdateRequest      $request
     * @param  int                $id
     * @return Detail
     * @throws ValidatorException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $user   = user();
        $params = $request->validated();
        $data   = $this->repository->updateConfig($user, $id, $params);

        return $this->success(new Detail($data), [], __p('mobile::phrase.config_updated_successfully'));
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
        $this->repository->deleteConfig(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('mobile::phrase.config_successfully_deleted'));
    }

    /**
     * Update active status.
     * @param  ActiveRequest $request
     * @param  int           $id
     * @return JsonResponse
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $config   = $this->repository->find($id);
        $isActive = Arr::get($params, 'active') ? 1 : 0;
        $config->update(['is_active' => $isActive]);
        $config->refresh();

        return $this->success(new Detail($config), [], __p('mobile::phrase.toggle_active_successfully', ['active' => $isActive]));
    }
}
