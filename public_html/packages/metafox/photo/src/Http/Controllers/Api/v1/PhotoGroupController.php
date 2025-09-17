<?php

namespace MetaFox\Photo\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Photo\Http\Requests\v1\PhotoGroup\ItemRequest;
use MetaFox\Photo\Http\Resources\v1\PhotoGroupItem\PhotoGroupItemItemCollection;
use MetaFox\Photo\Policies\PhotoGroupPolicy;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\Photo\Http\Controllers\Api\PhotoGroupController::$controllers;
 */

/**
 * Class PhotoGroupController.
 */
class PhotoGroupController extends ApiController
{
    /**
     * @var PhotoGroupRepositoryInterface
     */
    public $repository;

    public function __construct(PhotoGroupRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->viewPhotoGroup(user(), $id);

        $isApproved = (bool) $data->is_approved;

        /**
         * TODO: Improve this workflow to not return full of items in next version 5.1.6.
         */
        $items = match ($isApproved) {
            true    => $data->approvedItems,
            default => $data->items,
        };

        return $this->success(new PhotoGroupItemItemCollection($items));
    }

    public function items(ItemRequest $request, int $id): PhotoGroupItemItemCollection
    {
        $params = $request->validated();

        $context = user();

        $group = $this->repository->find($id);

        policy_authorize(PhotoGroupPolicy::class, 'view', $context, $group);

        $data = $this->repository->viewItems($context, $group, $params);

        return new PhotoGroupItemItemCollection($data);
    }
}
