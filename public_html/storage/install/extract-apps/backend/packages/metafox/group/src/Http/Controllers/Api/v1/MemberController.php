<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Group\Http\Requests\v1\Member\AddGroupAdminRequest;
use MetaFox\Group\Http\Requests\v1\Member\AddGroupModeratorRequest;
use MetaFox\Group\Http\Requests\v1\Member\CancelInvitePermissionRequest;
use MetaFox\Group\Http\Requests\v1\Member\ChangeToModeratorRequest;
use MetaFox\Group\Http\Requests\v1\Member\DeleteGroupAdminRequest;
use MetaFox\Group\Http\Requests\v1\Member\DeleteGroupMemberRequest;
use MetaFox\Group\Http\Requests\v1\Member\DeleteGroupModeratorRequest;
use MetaFox\Group\Http\Requests\v1\Member\IndexRequest;
use MetaFox\Group\Http\Requests\v1\Member\ReassignOwnerRequest;
use MetaFox\Group\Http\Requests\v1\Member\StoreRequest;
use MetaFox\Group\Http\Requests\v1\Member\UnJoinGroupRequest;
use MetaFox\Group\Http\Resources\v1\Group\GroupDetail as Detail;
use MetaFox\Group\Http\Resources\v1\Member\MemberItem;
use MetaFox\Group\Http\Resources\v1\Member\MemberItemCollection;
use MetaFox\Group\Models\Member;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\Membership;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class MemberController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @group group
 * @authenticated
 */
class MemberController extends ApiController
{
    /**
     * @var MemberRepositoryInterface
     */
    private MemberRepositoryInterface $memberRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private GroupRepositoryInterface $groupRepository;

    /**
     * MemberController constructor.
     *
     * @param MemberRepositoryInterface $memberRepository
     * @param GroupRepositoryInterface  $groupRepository
     */
    public function __construct(
        MemberRepositoryInterface $memberRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->memberRepository = $memberRepository;
        $this->groupRepository  = $groupRepository;
    }

    /**
     * Browse group members.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): JsonResource|MemberItemCollection
    {
        $params = $request->validated();
        $data   = $this->memberRepository->viewGroupMembers(user(), $params['group_id'], $params);

        return new MemberItemCollection($data);
    }

    /**
     * Create group member.
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

        $groupId = $params['group_id'];

        $group = $this->groupRepository->getGroup($groupId);

        $hasQuestions = Membership::hasMembershipQuestion($group);

        $mustAnswerQuestions = policy_check(GroupPolicy::class, 'answerQuestionBeforeJoining', $group);

        $result = null;

        if ($mustAnswerQuestions === false) {
            $result = $this->memberRepository->createRequest(user(), $groupId);
        }

        $data = [
            'id'                      => $groupId,
            'has_membership_question' => $hasQuestions,
        ];

        $dataDetail = new Detail($group->refresh());
        $response   = [
            'is_rule_confirmation' => $hasQuestions,
            'data'                 => is_array($result) ? $dataDetail : ($hasQuestions ? $data : null),
        ];

        return $this->success($response, [], is_array($result) ? $result['message'] : null);
    }

    /**
     * Delete group member.
     *
     * @param int                $id
     * @param UnJoinGroupRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(int $id, UnJoinGroupRequest $request): JsonResponse
    {
        $params = $request->validated();

        $result = $this->memberRepository->unJoinGroup(
            user(),
            $id,
            (bool) $params['not_invite_again'],
            $params['reassign_owner_id']
        );

        $message = __p('group::phrase.leave_group_successfully');

        if (Arr::has($result, 'group')) {
            return $this->success(Arr::get($result, 'group'), [], $message);
        }

        return $this->success($result, [], __p('group::phrase.leave_group_successfully'));
    }

    /**
     * Add group admin.
     *
     * @param AddGroupAdminRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function addGroupAdmins(AddGroupAdminRequest $request): JsonResponse
    {
        $params    = $request->validated();
        $groupId   = Arr::get($params, 'group_id');
        $userIds   = Arr::get($params, 'user_ids');

        $this->memberRepository->addGroupAdmins(user(), $groupId, $userIds);

        $groupMember = $this->memberRepository->getModel()->newQuery()
            ->with(['user', 'group'])
            ->where('group_id', $groupId)
            ->whereIn('user_id', $userIds)->get();

        $message = __p('group::phrase.add_group_admins_successfully');

        if (count($userIds) == 1) {
            $memberName = implode(' ', collect($groupMember)->pluck('user.full_name')->toArray());

            $message = __p('group::phrase.add_group_admin_successfully', [
                'member_name' => $memberName,
            ]);
        }

        return $this->success(new MemberItemCollection($groupMember), [], $message);
    }

    /**
     * Add group moderators.
     *
     * @param AddGroupModeratorRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function addGroupModerators(AddGroupModeratorRequest $request): JsonResponse
    {
        $params    = $request->validated();
        $groupId   = Arr::get($params, 'group_id');
        $userIds   = Arr::get($params, 'user_ids');
        $this->memberRepository->addGroupModerators(user(), $groupId, $userIds);

        $groupMember = $this->memberRepository->getModel()->newQuery()
            ->with(['user', 'group'])
            ->where('group_id', $groupId)
            ->whereIn('user_id', $userIds)->get();

        $message = __p('group::phrase.add_group_moderators_successfully');
        if (count($userIds) == 1) {
            $memberName = implode(' ', collect($groupMember)->pluck('user.full_name')->toArray());

            $message = __p('group::phrase.add_group_moderator_successfully', [
                'member_name' => $memberName,
            ]);
        }

        return $this->success(new MemberItemCollection($groupMember), [], $message);
    }

    /**
     * Change to moderator.
     *
     * @param ChangeToModeratorRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function changeToModerator(ChangeToModeratorRequest $request): JsonResponse
    {
        $params   = $request->validated();
        $groupId  = Arr::get($params, 'group_id');
        $userId   = Arr::get($params, 'user_id');

        $this->memberRepository->changeToModerator(user(), $groupId, $userId);

        $member   = $this->memberRepository->getGroupMember($groupId, $userId);
        $response = [];

        if ($member instanceof Member) {
            $response = new MemberItem($member);
        }

        return $this->success($response, [], __p('group::phrase.successfully_change_to_the_group_moderator'));
    }

    /**
     * Remove group admin.
     *
     * @param DeleteGroupAdminRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function removeGroupAdmin(DeleteGroupAdminRequest $request): JsonResponse
    {
        $params   = $request->validated();
        $groupId  = Arr::get($params, 'group_id');
        $userId   = Arr::get($params, 'user_id');
        $isDelete = Arr::get($params, 'is_delete');

        $this->memberRepository->removeGroupAdmin(user(), $groupId, $userId, $isDelete);

        $member   = $this->memberRepository->getGroupMember($groupId, $userId);
        $response = [
            'id' => (int) $groupId,
        ];

        if ($member instanceof Member) {
            $response = new MemberItem($member);
        }

        return $this->success($response, [], __p('group::phrase.remove_group_admin_successfully'));
    }

    /**
     * Remove group moderator.
     *
     * @param DeleteGroupModeratorRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function removeGroupModerator(DeleteGroupModeratorRequest $request): JsonResponse
    {
        $params   = $request->validated();
        $groupId  = Arr::get($params, 'group_id');
        $userId   = Arr::get($params, 'user_id');
        $isDelete = $params['is_delete'];

        $this->memberRepository->removeGroupModerator(user(), $groupId, $userId, $isDelete);

        $member   = $this->memberRepository->getGroupMember($groupId, $userId);
        $response = ['id' => (int) $groupId];

        if ($member instanceof Member) {
            $response = new MemberItem($member);
        }

        return $this->success($response, [], __p('group::phrase.remove_group_moderator_successfully'));
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
        $params   = $request->validated();
        $groupId  = Arr::get($params, 'group_id');
        $userId   = Arr::get($params, 'user_id');

        $result = $this->memberRepository->reassignOwner(user(), $groupId, $userId);

        if (!$result) {
            return $this->error(__p('group::phrase.the_user_is_not_a_group_admin'));
        }
        $groupMembers = $this->memberRepository->getGroupMembers($groupId);

        return $this->success(
            new MemberItemCollection($groupMembers),
            [],
            __p('group::phrase.successfully_reassign_the_group_owner')
        );
    }

    /**
     * Delete group member.
     *
     * @param DeleteGroupMemberRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function deleteGroupMember(DeleteGroupMemberRequest $request): JsonResponse
    {
        $params       = $request->validated();
        $groupId      = Arr::get($params, 'group_id');
        $userId       = Arr::get($params, 'user_id');
        $user         = UserEntity::getById($userId);
        $userFullName = $user->name;

        $deleteAllActivities = (bool) Arr::get($params, 'delete_activities', 0);
        $this->memberRepository->deleteGroupMember(user(), $groupId, $userId, $deleteAllActivities);

        return $this->success(
            [
                'id' => (int) $groupId,
            ],
            [],
            __p('group::phrase.user_full_name_has_been_removed_from_the_group', ['user_full_name' => $userFullName])
        );
    }

    /**
     * @param  CancelInvitePermissionRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function cancelInvitePermission(CancelInvitePermissionRequest $request): JsonResponse
    {
        $params   = $request->validated();
        $groupId  = Arr::get($params, 'group_id');
        $userId   = Arr::get($params, 'user_id');
        $user     = UserEntity::getById($userId);

        $this->memberRepository->cancelInvitePermission(user(), $groupId, $userId);

        $groupMember = $this->memberRepository->getGroupMember($groupId, $userId);
        $message     = __p('group::phrase.user_full_name_invited_you_to_became_an_admin_was_cancelled', [
            'role'     => $params['invite_type'],
            'username' => $user->name,
        ]);

        return $this->success(new MemberItem($groupMember), [], $message);
    }
}
