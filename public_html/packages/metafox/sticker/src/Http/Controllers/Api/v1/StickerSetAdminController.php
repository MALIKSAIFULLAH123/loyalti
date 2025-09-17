<?php

namespace MetaFox\Sticker\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\Sticker\Http\Requests\v1\StickerSet\Admin\IndexRequest;
use MetaFox\Sticker\Http\Requests\v1\StickerSet\Admin\StoreRequest;
use MetaFox\Sticker\Http\Requests\v1\StickerSet\Admin\UpdateRequest;
use MetaFox\Sticker\Http\Resources\v1\StickerSet\Admin\StickerSetDetail as Detail;
use MetaFox\Sticker\Http\Resources\v1\StickerSet\Admin\StickerSetItemCollection as ItemCollection;
use MetaFox\Sticker\Http\Resources\v1\StickerSet\Admin\StoreStickerSetForm;
use MetaFox\Sticker\Http\Resources\v1\StickerSet\Admin\UpdateStickerSetForm;
use MetaFox\Sticker\Repositories\StickerSetAdminRepositoryInterface;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Sticker\Http\Controllers\Api\StickerSetAdminController::$controllers;.
 */

/**
 * Class StickerSetAdminController.
 * @codeCoverageIgnore
 * @ignore
 * @group sticker
 */
class StickerSetAdminController extends ApiController
{
    /**
     * @var StickerSetAdminRepositoryInterface
     */
    private StickerSetAdminRepositoryInterface $repository;

    /**
     * StickerSetAdminController Constructor.
     *
     * @param StickerSetAdminRepositoryInterface $repository
     */
    public function __construct(StickerSetAdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return JsonResponse
     * @throws AuthorizationException | AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->viewStickerSets(user(), $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->installStickerSet(user(), $params);

        $this->navigate($data->admin_browse_url);

        return $this->success(new Detail($data), [], __p('sticker::phrase.successfully_created_sticker'));
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->viewStickerSet(user(), $id);

        return $this->success(new Detail($data));
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $data = $this->repository->updateStickerSet(user(), $id, $params);
        $this->navigate($data->admin_browse_url);

        return $this->success(new Detail($data), [], __p('sticker::phrase.successfully_updated_sticker'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteStickerSet(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('sticker::phrase.successfully_deleted_sticker'));
    }

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $isActive = $request->get('active');
        $this->repository->toggleActive(user(), $id, $isActive);

        return $this->success([
            'is_active' => $isActive,
        ], [], __p('core::phrase.updated_successfully'));
    }

    public function edit(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        return $this->success(new UpdateStickerSetForm($item));
    }

    public function create(Request $request): JsonResponse
    {
        $form = resolve(StoreStickerSetForm::class);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }
}
