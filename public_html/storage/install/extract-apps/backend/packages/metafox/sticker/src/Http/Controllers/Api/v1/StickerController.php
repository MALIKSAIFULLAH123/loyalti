<?php

namespace MetaFox\Sticker\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Sticker\Http\Requests\v1\Sticker\CreateRecentRequest;
use MetaFox\Sticker\Http\Requests\v1\Sticker\IndexRequest;
use MetaFox\Sticker\Http\Requests\v1\Sticker\ViewRecentRequest;
use MetaFox\Sticker\Http\Resources\v1\Sticker\StickerItemCollection;
use MetaFox\Sticker\Repositories\StickerRecentRepositoryInterface;
use MetaFox\Sticker\Repositories\StickerRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Sticker\Http\Controllers\Api\StickerController::$controllers;.
 */

/**
 * Class StickerController.
 * @codeCoverageIgnore
 * @ignore
 * @authenticated
 * @group sticker
 */
class StickerController extends ApiController
{
    /**
     * @var StickerRepositoryInterface
     */
    private StickerRepositoryInterface $repository;

    private StickerRecentRepositoryInterface $recentRepository;

    /**
     * StickerController Constructor.
     *
     * @param StickerRepositoryInterface       $repository
     * @param StickerRecentRepositoryInterface $recentRepository
     */
    public function __construct(
        StickerRepositoryInterface       $repository,
        StickerRecentRepositoryInterface $recentRepository,
    )
    {
        $this->repository = $repository;
        $this->recentRepository = $recentRepository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return StickerItemCollection
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): StickerItemCollection
    {
        $context = user();
        $params = $request->validated();
        $data = $this->repository->viewStickers($context, $params);

        return new StickerItemCollection($data);
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteSticker(user(), $id);

        return $this->success([
            'id' => $id,
        ]);
    }

    /**
     * @throws AuthenticationException
     */
    public function recent(ViewRecentRequest $request): JsonResponse
    {
        $params = $request->validated();
        $result = $this->repository->viewRecentStickers(user(), $params);

        return $this->success(new StickerItemCollection($result));
    }

    /**
     * @throws AuthenticationException
     */
    public function storeRecent(CreateRecentRequest $request): JsonResponse
    {
        $params = $request->validated();
        $result = $this->recentRepository->createRecentSticker(user(), $params['sticker_id']);

        return $this->success($result);
    }
}
