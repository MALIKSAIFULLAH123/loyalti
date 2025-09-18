<?php

namespace MetaFox\Sticker\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\ActiveRequest;
use MetaFox\Sticker\Http\Requests\v1\StickerSet\AddUserSetRequest;
use MetaFox\Sticker\Http\Requests\v1\StickerSet\IndexRequest;
use MetaFox\Sticker\Http\Resources\v1\StickerSet\StickerSetDetail as Detail;
use MetaFox\Sticker\Http\Resources\v1\StickerSet\StickerSetItemCollection as ItemCollection;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;

/**
 * Class StickerSetController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group sticker
 */
class StickerSetController extends ApiController
{
    /**
     * @var StickerSetRepositoryInterface
     */
    private StickerSetRepositoryInterface $repository;

    /**
     * @param StickerSetRepositoryInterface $repository
     */
    public function __construct(StickerSetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse sticker.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();
        $data   = $this->repository->viewStickerSets(user(), $params);

        return new ItemCollection($data);
    }

    /**
     * View sticker set.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewStickerSet(user(), $id);

        return new Detail($data);
    }

    /**
     * Active sticker set.
     *
     * @param  ActiveRequest           $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function toggleActive(ActiveRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $this->repository->toggleActive(user(), $id, $params['is_active']);

        return $this->success([
            'id' => $id,
        ]);
    }

    /**
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function toggleDefault(int $id): JsonResponse
    {
        $context = user();

        //        $this->repository->markAsDefault($context, $id);

        return $this->success([], []);
    }

    /**
     * @param AddUserSetRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function addUserStickerSet(AddUserSetRequest $request): JsonResponse
    {
        $params = $request->validated();
        $id     = Arr::get($params, 'id', 0);
        $this->repository->addUserStickerSet(user(), $id);

        return $this->success([]);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function deleteUserStickerSet(int $id): JsonResponse
    {
        $this->repository->deleteUserStickerSet(user(), $id);

        return $this->success([]);
    }
}
