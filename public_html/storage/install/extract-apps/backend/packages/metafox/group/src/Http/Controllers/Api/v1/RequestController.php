<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Http\Requests\v1\Request\AcceptMemberRequest;
use MetaFox\Group\Http\Requests\v1\Request\DeclineMemberRequest;
use MetaFox\Group\Http\Requests\v1\Request\IndexRequest;
use MetaFox\Group\Http\Resources\v1\Request\RequestItem;
use MetaFox\Group\Http\Resources\v1\Request\RequestItemCollection;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Policies\RequestPolicy;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\Group\Support\Membership;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\RequestController::$controllers.
 */

/**
 * Class RequestController.
 * @ignore
 * @codeCoverageIgnore
 * @group group
 * @authenticated
 */
class RequestController extends ApiController
{
    /**
     * @var RequestRepositoryInterface
     */
    private RequestRepositoryInterface $repository;
    private MemberRepositoryInterface  $memberRepository;

    /**
     * RequestController constructor.
     *
     * @param RequestRepositoryInterface $repository
     */
    public function __construct(RequestRepositoryInterface $repository, MemberRepositoryInterface $memberRepository)
    {
        $this->repository       = $repository;
        $this->memberRepository = $memberRepository;
    }

    /**
     * Browse member requests in a group.
     *
     * @return JsonResource
     * @throws AuthenticationException|AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->viewRequests(user(), $params);

        return new RequestItemCollection($data);
    }

    /**
     * Accept member request.
     *
     * @param AcceptMemberRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     * @throws ValidationException
     */
    public function acceptMemberRequest(AcceptMemberRequest $request): JsonResponse
    {
        $params       = $request->validated();
        $groupId      = $params['group_id'];
        $userId       = $params['user_id'];
        $user         = UserEntity::getById($userId);
        $userFullName = $user->name;

        if ($this->memberRepository->isGroupMember($groupId, $userId)) {
            return $this->error(__p('group::phrase.this_user_id_already_a_group_member'));
        }

        $result = $this->repository->acceptMemberRequest(user(), $groupId, $userId);
        $this->memberRepository->addGroupMember(Group::find($groupId), $userId);

        return $this->success(
            new RequestItem($result),
            [],
            __p('group::phrase.user_full_name_has_been_accepted', ['user_full_name' => $userFullName])
        );
    }

    /**
     * Deny member request.
     *
     * @param AcceptMemberRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function denyMemberRequest(AcceptMemberRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $groupId = $params['group_id'];
        $userId  = $params['user_id'];

        $result = $this->repository->denyMemberRequest(user(), $groupId, $userId);

        return $this->success([
            new RequestItem($result),
        ], [], __p('group::phrase.decline_successfully'));
    }

    /**
     * Cancel a member request.
     *
     * @param int $groupId
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function cancelRequest(int $groupId): JsonResponse
    {
        $result = $this->repository->cancelRequest(user(), $groupId);

        if (!$result) {
            return $this->error(__p('group::validation.the_request_join_group_does_not_exist'), 403);
        }

        return $this->success([
            'id'         => $groupId,
            'membership' => Membership::NO_JOIN,
        ], [], __p('group::phrase.successfully_deleted_request_for_this_group'));
    }

    /**
     * Accept member request.
     *
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function accept(int $id): JsonResponse
    {
        $request = $this->repository->find($id);
        $context = user();

        policy_authorize(RequestPolicy::class, 'approve', $context, $request);

        if ($this->memberRepository->isGroupMember($request->group_id, $request->user_id)) {
            return $this->error(__p('group::phrase.this_user_id_already_a_group_member'));
        }

        $result = $this->repository->acceptRequest($context, $request);
        $this->memberRepository->addGroupMember($request->group, $request->user_id);

        return $this->success(
            new RequestItem($result),
            [],
            __p('group::phrase.user_full_name_has_been_accepted', ['user_full_name' => $request->user->full_name])
        );
    }

    /**
     * @param  DeclineMemberRequest    $request
     * @param  int                     $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function decline(DeclineMemberRequest $request, int $id): JsonResponse
    {
        $params   = $request->validated();
        $resource = $this->repository->find($id);
        $context  = user();

        policy_authorize(RequestPolicy::class, 'approve', $context, $resource);

        if ($this->memberRepository->isGroupMember($resource->group_id, $resource->user_id)) {
            return $this->error(__p('group::phrase.this_user_id_already_a_group_member'));
        }

        $result = $this->repository->declineRequest($context, $resource, $params);

        return $this->success(
            new RequestItem($result),
            [],
            __p('group::phrase.decline_successfully')
        );
    }
}
