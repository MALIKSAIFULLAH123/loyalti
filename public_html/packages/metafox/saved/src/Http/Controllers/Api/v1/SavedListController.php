<?php

namespace MetaFox\Saved\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Saved\Http\Requests\v1\SavedList\IndexRequest;
use MetaFox\Saved\Http\Requests\v1\SavedList\ManageFriendListRequest;
use MetaFox\Saved\Http\Requests\v1\SavedList\StoreRequest;
use MetaFox\Saved\Http\Requests\v1\SavedList\UpdateRequest;
use MetaFox\Saved\Http\Requests\v1\SavedListMember\RemoveMemberRequest;
use MetaFox\Saved\Http\Resources\v1\SavedList\SavedListDataItemCollection;
use MetaFox\Saved\Http\Resources\v1\SavedList\SavedListDetail as Detail;
use MetaFox\Saved\Http\Resources\v1\SavedList\SavedListItemCollection as ItemCollection;
use MetaFox\Saved\Http\Resources\v1\SavedList\StoreSavedListForm;
use MetaFox\Saved\Http\Resources\v1\SavedList\UpdateSavedListForm;
use MetaFox\Saved\Http\Resources\v1\SavedListMember\MemberItemCollection;
use MetaFox\Saved\Policies\SavedListPolicy;
use MetaFox\Saved\Repositories\SavedListMemberRepositoryInterface;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;

/**
 * Class SavedListController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group saved
 */
class SavedListController extends ApiController
{
    /**
     * SavedListController Constructor.
     *
     * @param SavedListRepositoryInterface       $repository
     * @param SavedListMemberRepositoryInterface $memberRepository
     */
    public function __construct(
        protected SavedListRepositoryInterface $repository,
        protected SavedListMemberRepositoryInterface $memberRepository
    ) {
    }

    /**
     * Browse list.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params         = $request->validated();
        $data           = $this->repository->viewSavedLists(user(), $params);
        $totalSavedList = $this->repository->getTotalSavedLists(user(), $params);

        $collection = new ItemCollection($data);
        $collection->setExtraMeta([
            'current_page' => $params['page'],
            'total'        => $totalSavedList,
        ]);

        return $collection;
    }

    /**
     * Create list.
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
        $data   = $this->repository->createSavedList(user(), $params);

        return $this->success(new Detail($data), [], __p('saved::phrase.collection_successfully_created'));
    }

    /**
     * View list.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->repository->viewSavedList(user(), $id);

        return $this->success(new Detail($data));
    }

    /**
     * Update list.
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
        $data   = $this->repository->updateSavedList(user(), $id, $params);

        return $this->success(new Detail($data), [], __p('saved::phrase.collection_successfully_updated'));
    }

    /**
     * Remove list.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->deleteSavedList(user(), $id);

        $this->navigate('saved');

        return $this->success([
            'id' => $id,
        ], [], __p('saved::phrase.collection_successfully_deleted'));
    }

    /**
     * View creation form.
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function formStore(): JsonResponse
    {
        $context = user();

        policy_authorize(SavedListPolicy::class, 'create', $context, null);

        return $this->success(new StoreSavedListForm());
    }

    /**
     * View update form.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function formUpdate(int $id): JsonResponse
    {
        $context   = user();

        $savedList = $this->repository->find($id);
        policy_authorize(SavedListPolicy::class, 'update', $context, $savedList);

        return $this->success(new UpdateSavedListForm($savedList), [], '');
    }

    /**
     * @param  ManageFriendListRequest $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function addFriends(ManageFriendListRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $this->repository->addFriendToSavedList(user(), $id, $params['user_ids']);

        return $this->success([], [], __p('saved::phrase.updated_friend_to_list_successfully'));
    }

    /**
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function viewFriends(int $id): JsonResponse
    {
        $context   = user();
        $savedList = $this->repository->find($id);

        policy_authorize(SavedListPolicy::class, 'viewMember', $context, $savedList);

        $members = $this->memberRepository->viewSavedListMembers($context, $id);

        return $this->success(new MemberItemCollection($members));
    }

    /**
     * @param  RemoveMemberRequest     $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function removeMember(RemoveMemberRequest $request, int $id): JsonResponse
    {
        $context   = user();
        $params    = $request->validated();
        $savedList = $this->repository->find($id);
        $userId    = Arr::get($params, 'user_id');

        policy_authorize(SavedListPolicy::class, 'removeMember', $context, $savedList, $userId);

        $deleted = $this->memberRepository->removeMember($context, $id, $userId);

        if ($deleted) {
            return $this->success([], [], __p('saved::phrase.remove_member_successfully'));
        }

        return $this->error(__p('saved::phrase.cannot_remove_this_member'));
    }

    /**
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function leaveCollection(int $id): JsonResponse
    {
        $context   = user();
        $savedList = $this->repository->find($id);

        policy_authorize(SavedListPolicy::class, 'leaveCollection', $context, $savedList);

        $leaved = $this->memberRepository->removeMember($context, $id, $context->entityId());

        if ($leaved) {
            return $this->success([], [], __p('saved::phrase.leave_collection_successfully'));
        }

        return $this->error(__p('saved::phrase.action_cannot_be_done'));
    }

    /**
     * @param  IndexRequest            $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function viewItemCollection(IndexRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        Arr::set($params, 'id', $id);
        $data = $this->repository->viewItemCollection(user(), $params);

        return $this->success(new SavedListDataItemCollection($data));
    }
}
