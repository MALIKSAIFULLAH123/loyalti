<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Page\Http\Requests\v1\PageMember\AddPageAdminRequest;
use MetaFox\Page\Http\Requests\v1\PageMember\CancelAdminInviteRequest;
use MetaFox\Page\Http\Requests\v1\PageMember\DeletePageAdminRequest;
use MetaFox\Page\Http\Requests\v1\PageMember\DeletePageMemberRequest;
use MetaFox\Page\Http\Requests\v1\PageMember\IndexRequest;
use MetaFox\Page\Http\Requests\v1\PageMember\ReassignOwnerRequest;
use MetaFox\Page\Http\Requests\v1\PageMember\StoreRequest;
use MetaFox\Page\Http\Requests\v1\PageMember\UpdateRequest;
use MetaFox\Page\Http\Resources\v1\Page\PageDetail;
use MetaFox\Page\Http\Resources\v1\PageMember\PageMemberItemCollection;
use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Policies\PageMemberPolicy;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class PageMemberController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageMemberController extends ApiController
{
    public PageMemberRepositoryInterface $repository;

    public function __construct(PageMemberRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data = $this->repository->viewPageMembers(user(), $params['page_id'], $params);

        return new PageMemberItemCollection($data);
    }

    /**
     * Display a listing of the admin resource.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function viewPageAdmins(IndexRequest $request)
    {
        $params = $request->validated();
        $data = $this->repository->viewPageMembers(user(), $params['page_id'], $params);

        return new PageMemberItemCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();
        $context = user();

        $page = resolve(PageRepositoryInterface::class)->find($params['page_id']);

        policy_authorize(PageMemberPolicy::class, 'likePage', $context, $page);

        $this->repository->likePage($context, $page->entityId());

        return $this->success(new PageDetail($page->refresh()), [], __p('page::phrase.liked_successfully'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $memberType = $params['member_type'];
        $userId = $params['user_id'];
        $context = user();

        $this->repository->updatePageMember($context, $id, $userId, $memberType);

        $message = __p('page::phrase.member_updated_to_admin_successfully');

        if ($memberType == PageMember::MEMBER) {
            $message = __p('page::phrase.admin_updated_to_member_successfully');
        }

        return $this->success([], [], $message);
    }

    /**
     * @param AddPageAdminRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function addPageAdmins(AddPageAdminRequest $request): JsonResponse
    {
        $params = $request->validated();
        $pageId = $params['page_id'];
        $userIds = $params['user_ids'];
        $this->repository->addPageAdmins(user(), $pageId, $userIds);

        $data = $this->repository->getPageMembers($pageId);

        $total = count($userIds);
        $message = __p('page::phrase.successfully_invited_a_large_member_to_be_the_admin');
        if ($total == 1) {
            $userId = (int)implode($userIds);
            $userName = UserEntity::getById($userId)->detail->full_name;

            $message = __p('page::phrase.successfully_invited_a_member_to_be_the_admin', ['username' => $userName]);
        }

        return $this->success(new PageMemberItemCollection($data), [], $message);
    }

    /**
     * @param DeletePageAdminRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function deletePageAdmin(DeletePageAdminRequest $request): JsonResponse
    {
        $params = $request->validated();
        $pageId = $params['page_id'];
        $userId = $params['user_id'];

        $result = $this->repository->deletePageAdmin(user(), $pageId, $userId);

        if (false == $result) {
            return $this->error(__p('page::phrase.the_user_is_not_a_page_admin'));
        }

        $data = $this->repository->getPageMembers($pageId);

        return $this->success(
            new PageMemberItemCollection($data),
            [],
            __p('page::phrase.remove_page_admin_successfully')
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->repository->unLikePage(user(), $id);

        $page = resolve(PageRepositoryInterface::class)->find($id);

        return $this->success(new PageDetail($page), [], __p('page::phrase.unliked_successfully'));
    }

    /**
     * Reassign group owner.
     *
     * @param ReassignOwnerRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function reassignOwner(ReassignOwnerRequest $request): JsonResponse
    {
        $params = $request->validated();
        $pageId = $params['page_id'];
        $userId = $params['user_id'];

        $result = $this->repository->reassignOwner(user(), $pageId, $userId);

        if (!$result) {
            return $this->error(__p('page::phrase.the_user_is_not_a_page_admin'));
        }
        $pageMember = $this->repository->getPageMembers($pageId);

        return $this->success(
            new PageMemberItemCollection($pageMember),
            [],
            __p('page::phrase.successfully_reassign_the_page_owner')
        );
    }

    /**
     * Delete group member.
     *
     * @param DeletePageMemberRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function deletePageMember(DeletePageMemberRequest $request): JsonResponse
    {
        $params = $request->validated();
        $pageId = Arr::get($params, 'page_id');
        $userId = Arr::get($params, 'user_id');
        $user = UserEntity::getById($userId);
        $userFullName = $user->name;

        $this->repository->deletePageMember(user(), $pageId, $userId, $params);

        $data = $this->repository->getPageMembers($pageId);

        return $this->success(
            new PageMemberItemCollection($data),
            [],
            __p('page::phrase.user_full_name_has_been_removed_from_the_page', ['user_full_name' => $userFullName])
        );
    }

    /**
     * Delete group member.
     *
     * @param DeletePageAdminRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function removePageAdmin(DeletePageAdminRequest $request): JsonResponse
    {
        $params = $request->validated();
        $pageId = $params['page_id'];
        $userId = $params['user_id'];
        $isDelete = $params['is_delete'];

        $this->repository->removePageAdmin(user(), $pageId, $userId, $isDelete);

        return $this->success([
            'id' => (int)$pageId,
        ], [], __p('page::phrase.remove_page_admin_successfully'));
    }

    /**
     * @param CancelAdminInviteRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function cancelAdminInvite(CancelAdminInviteRequest $request): JsonResponse
    {
        $params = $request->validated();

        $pageId = $params['page_id'];
        $userId = $params['user_id'];
        $user = UserEntity::getById($userId);

        $this->repository->cancelAdminInvite(user(), $pageId, $userId);

        $pageMember = $this->repository->getPageMembers($pageId);
        $message = __p('page::phrase.user_full_name_invited_you_to_became_an_admin_was_cancelled', [
            'role'     => $params['invite_type'],
            'username' => $user->name,
        ]);

        return $this->success(new PageMemberItemCollection($pageMember), [], $message);
    }
}
