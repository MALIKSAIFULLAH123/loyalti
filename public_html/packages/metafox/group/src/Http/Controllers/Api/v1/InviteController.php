<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Group\Http\Requests\v1\Invite\CancelRequest;
use MetaFox\Group\Http\Requests\v1\Invite\DeleteGroupInviteRequest;
use MetaFox\Group\Http\Requests\v1\Invite\IndexRequest;
use MetaFox\Group\Http\Requests\v1\Invite\StoreRequest;
use MetaFox\Group\Http\Requests\v1\Invite\UpdateRequest;
use MetaFox\Group\Http\Resources\v1\Group\GroupDetail as Detail;
use MetaFox\Group\Http\Resources\v1\Invite\InviteItemCollection;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Policies\InvitePolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Support\InviteType;
use MetaFox\Group\Support\Membership;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 * Assign this class in $controllers of
 *
 * @link \MetaFox\Group\Http\Controllers\Api\InviteController::$controllers;
 */

/**
 * Class InviteController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @ignore
 * @codeCoverageIgnore
 * @group group
 * @authenticated
 */
class InviteController extends ApiController
{
    /**
     * InviteController constructor.
     *
     * @param InviteRepositoryInterface $repository
     * @param GroupRepositoryInterface  $groupRepository
     */
    public function __construct(
        protected InviteRepositoryInterface $repository,
        protected GroupRepositoryInterface  $groupRepository,
        protected MemberRepositoryInterface $memberRepository,
    ) {}

    /**
     * Browse group invitation.
     *
     * @param IndexRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException|AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params  = $request->validated();
        $results = $this->repository->viewInvites(user(), $params);

        return new InviteItemCollection($results);
    }

    /**
     * Store group invitation.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException|ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $group   = $this->groupRepository->find($params['group_id']);

        policy_authorize(GroupPolicy::class, 'invite', $context, $group);

        $this->repository->inviteFriends($context, $params['group_id'], $params['ids']);

        return $this->success([], [], __p('group::phrase.member_s_have_been_invited_to_join_this_group'));
    }

    /**
     * Update group invitation.
     *
     * @param UpdateRequest $request
     *
     * @return JsonResponse
     * @throws ValidatorException
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $params     = $request->validated();
        $groupId    = Arr::get($params, 'group_id');
        $group      = $this->groupRepository->find($groupId);
        $context    = user();
        $isAccept   = (bool) $params['accept'];
        $inviteCode = Arr::get($params, 'invite_code');

        $invite = $inviteCode != null
            ? $this->repository->getInviteByCode($context, $groupId, $inviteCode)
            : $this->repository->getPendingInvite($groupId, $context);

        if (!$invite instanceof Invite || !$invite?->isPending()) {
            $message = $inviteCode != null
                ? json_encode([
                    'title'   => __p('core::phrase.the_link_you_followed_has_expired_title'),
                    'message' => __p('core::phrase.the_link_you_followed_has_expired'),
                ]) : json_encode([
                    'title'   => __p('group::phrase.message'),
                    'message' => __p('group::phrase.message_error_when_empty_invite'),
                ]);
            abort(403, $message);
        }

        policy_authorize(GroupPolicy::class, 'acceptOrDeclineInvite', $context, $group, $invite?->invite_type, $invite?->code);

        if ($isAccept === true) {
            if (!$group->isMember($context)) {
                if (policy_check(GroupPolicy::class, 'answerQuestionBeforeJoining', $group)) {
                    return $this->success([
                        'is_rule_confirmation' => true,
                    ]);
                }
            }

            $result = $this->repository->acceptInvite($group, $context);

            if (!$result) {
                return $this->error(__p('group::phrase.message_error_when_empty_invite'), 403);
            }

            $message = $this->repository->getMessageAcceptInvite($group->refresh(), $context, $invite?->invite_type);

            return $this->success([
                'data' => new Detail($group->refresh()),
            ], [], $message);
        }

        $result = $this->repository->declineInvite($group, $context);
        if (!$result) {
            return $this->error(__p('group::phrase.message_error_when_empty_invite'), 403);
        }
        $membership = Membership::getMembership($group->refresh(), $context);
        $data       = [
            'id'           => $group->entityId(),
            'total_member' => $group->total_member,
            'membership'   => $membership,
        ];

        if (
            $group->isSecretPrivacy() && in_array(
                $invite->getInviteType(),
                [InviteType::INVITED_MEMBER, InviteType::INVITED_GENERATE_LINK]
            )
        ) {
            $data['redirect_url'] = url_utility()->makeApiUrl('/group');
        }

        return $this->success($data, [], __p('group::phrase.denied_successfully'));
    }

    /**
     * Remove group invitation.
     *
     * @param DeleteGroupInviteRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function deleteGroupInvite(DeleteGroupInviteRequest $request): JsonResponse
    {
        $params = $request->validated();
        $this->repository->deleteGroupInvite(user(), $params['group_id'], $params['user_id']);

        $user = UserEntity::getById($params['user_id'])->detail;

        return $this->success([
            'user' => $user,
        ], [], __p('group::phrase.successfully_deleted_invite_friend', ['userName' => $user->full_name]));
    }

    /**
     * Remove group invitation.
     *
     * @param CancelRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function cancelInvite(CancelRequest $request): JsonResponse
    {
        $params = $request->validated();

        Arr::set($params, 'status_id', Invite::STATUS_CANCELLED);
        $this->repository->updateInvite(user(), $params);

        $user = UserEntity::getById($params['user_id'])->detail;

        return $this->success([
            'user' => $user,
        ], [], __p('group::phrase.successfully_deleted_invite_friend', ['userName' => $user->full_name]));
    }

    /**
     * Cancel group invitation.
     *
     * @param int $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function cancel(int $id): JsonResponse
    {
        $context = user();
        $invite  = $this->repository->find($id);

        policy_authorize(InvitePolicy::class, 'cancelInvite', $context, $invite);

        $result = $invite->update(['status_id' => Invite::STATUS_CANCELLED]);

        if (!$result) {
            return $this->error();
        }

        $message = __p('group::phrase.successfully_deleted_invite_friend', ['userName' => $invite->user?->full_name]);

        if ($invite->isInviteAdmin() || $invite->isInviteModerator()) {
            $message = __p('group::phrase.user_full_name_invited_you_to_became_an_admin_was_cancelled', [
                'role'     => $invite->invite_type,
                'username' => $invite->user?->full_name,
            ]);
        }

        return $this->success([], [], $message);
    }
}
