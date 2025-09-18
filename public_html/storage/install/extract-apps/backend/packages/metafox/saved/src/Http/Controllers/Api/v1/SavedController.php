<?php

namespace MetaFox\Saved\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Saved\Http\Requests\v1\Saved\IndexRequest;
use MetaFox\Saved\Http\Requests\v1\Saved\MoveItemRequest;
use MetaFox\Saved\Http\Requests\v1\Saved\StoreRequest;
use MetaFox\Saved\Http\Requests\v1\Saved\UnSaveRequest;
use MetaFox\Saved\Http\Requests\v1\Saved\UpdateRequest;
use MetaFox\Saved\Http\Requests\v1\Saved\ViewRequest;
use MetaFox\Saved\Http\Resources\v1\Saved\SavedDetail as Detail;
use MetaFox\Saved\Http\Resources\v1\Saved\SavedItemCollection as ItemCollection;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Repositories\SavedListItemViewRepositoryInterface;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;

/**
 * Class SavedController.
 * @ignore
 * @codeCoverageIgnore
 * @group saved
 * @authenticated
 */
class SavedController extends ApiController
{
    /**
     * SavedController Constructor.
     *
     * @param SavedRepositoryInterface             $repository
     * @param SavedListItemViewRepositoryInterface $itemViewRepository
     */
    public function __construct(
        protected SavedRepositoryInterface $repository,
        protected SavedListItemViewRepositoryInterface $itemViewRepository
    ) {
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $data    = $this->repository->viewSavedItems($context, $params);

        $itemCollection = new ItemCollection($data);
        $itemCollection->setCollectionId($params['collection_id']);

        return $this->success($itemCollection);
    }

    /**
     * Create item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $data = $this->repository->createSaved(user(), $params);

        return $this->success([
            'id'       => $data->entityId(),
            'is_saved' => true,
        ], [], __p('saved::phrase.saved_successfully', ['entity_name' => $data->getItemTypeName()]));
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->viewSavedItem(user(), $id);

        return $this->success(new Detail($data));
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $data   = $this->repository->updateSaved(user(), $id, $params);

        return $this->success(new Detail($data), [], __p('saved::phrase.saved_item_updated_successfully'));
    }

    /**
     * Remove item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteSaved(user(), $id);

        return $this->success([
            'id'      => $id,
            'is_save' => 0,
        ], [], __p('saved::phrase.unsaved_item_successfully'));
    }

    /**
     * Unsave item.
     *
     * @param UnSaveRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function unSave(UnSaveRequest $request): JsonResponse
    {
        $params = $request->validated();

        $savedItem = $this->repository->findSavedItem(user(), $params);

        if (!$savedItem instanceof Saved) {
            return $this->error(__p('saved::phrase.please_update_timestamp'), 403);
        }

        $this->repository->unSave(user(), $params);

        return $this->success([
            'is_save' => 0,
        ], [], __p('saved::phrase.unsaved_successfully', ['entity_name' => $savedItem->getItemTypeName()]));
    }

    /**
     * Browse tabs.
     *
     * @return JsonResponse
     */
    public function getTabs(): JsonResponse
    {
        $data = $this->repository->getFilterOptions();

        return $this->success($data);
    }

    /**
     * Move item.
     *
     * @throws AuthenticationException
     */
    public function moveItem(MoveItemRequest $request): JsonResponse
    {
        $params = $request->validated();
        $user   = user();

        $itemId        = $params['item_id'];
        $collectionIds = $params['collection_ids'];

        $item    = $this->repository->addToList($user, $itemId, $collectionIds);
        $message = null;

        if (!empty($collectionIds)) {
            $message = __p('saved::phrase.added_item_to_collection_successfully', ['value' => count($collectionIds)]);
        }

        return $this->success(new Detail($item), [], $message);
    }

    /**
     * Mark as opened.
     *
     * @throws AuthenticationException
     */
    public function markAsOpened(ViewRequest $request): JsonResponse
    {
        $user   = user();
        $params = $request->validated();
        $status = Arr::get($params, 'status', 1);
        $listId = Arr::get($params, 'collection_id');

        switch ($status) {
            case 1:
                $data    = $this->itemViewRepository->markAsOpened($user, $params);
                $message = __p('saved::phrase.item_is_marked_as_opened_successfully');
                break;
            default:
                $data    = $this->itemViewRepository->markAsUnOpened($user, $params);
                $message = __p('saved::phrase.item_is_marked_as_unopened_successfully');
                break;
        }

        $resource = new Detail($data);

        return $this->success($resource->setCollectionId($listId), [], $message);
    }

    public function removeCollectionItem(int $list_id, int $saved_id)
    {
        $context = user();
        $params  = [
            'collection_id' => $list_id,
            'saved_id'      => $saved_id,
        ];

        $this->repository->removeCollectionItem($context, $params);

        return $this->success(null, [], __p('saved::phrase.item_is_removed_from_collection_successfully'));
    }
}
