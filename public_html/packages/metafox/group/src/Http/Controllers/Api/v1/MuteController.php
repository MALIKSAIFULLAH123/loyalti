<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Group\Http\Requests\v1\Mute\DeleteRequest;
use MetaFox\Group\Http\Requests\v1\Mute\IndexRequest;
use MetaFox\Group\Http\Requests\v1\Mute\StoreRequest;
use MetaFox\Group\Http\Resources\v1\Member\MemberItem;
use MetaFox\Group\Http\Resources\v1\Mute\MuteItem;
use MetaFox\Group\Http\Resources\v1\Mute\MuteItemCollection as ItemCollection;
use MetaFox\Group\Models\Mute;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Repositories\MuteRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Group\Http\Controllers\Api\MuteController::$controllers;.
 */

/**
 * Class MuteController.
 * @codeCoverageIgnore
 * @ignore
 */
class MuteController extends ApiController
{
    /**
     * MuteController Constructor.
     *
     * @param MuteRepositoryInterface   $repository
     * @param MemberRepositoryInterface $memberRepository
     */
    public function __construct(
        protected MuteRepositoryInterface   $repository,
        protected MemberRepositoryInterface $memberRepository
    )
    {
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = user();
        $data    = $this->repository->viewMutedUsersInGroup($context, $params['group_id'], $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $groupId = $params['group_id'];
        $userId  = $params['user_id'];
        $result  = $this->repository->muteInGroup(user(), $groupId, $params);

        if (!$result) {
            return $this->error();
        }

        /** @var Mute $userMuted */
        $userMuted = $this->repository->getUserMuted($groupId, $userId);

        $dateTime = 0;

        $user = UserEntity::getById($userId);

        if (null !== $userMuted?->expired_at) {
            $dateTime = Carbon::parse($userMuted->expired_at)->format('c');
        }

        $resource = new MuteItem($userMuted);

        if (MetaFox::isMobile()) {
            $member   = $this->memberRepository->getGroupMember($groupId, $userId);
            $resource = new MemberItem($member);
        }

        if (0 == $dateTime) {
            return $this->success(
                $resource,
                [],
                __p('group::phrase.member_successfully_muted', ['date_time' => $dateTime])
            );
        }

        return $this->success($resource, [
            'alert' => [
                'message'   => 'member_successfully_muted_with_expired_time',
                'arguments' => [
                    'message' => [
                        [
                            'key'   => 'user_name',
                            'value' => $user->name,
                        ],
                        [
                            'key'     => 'date_time',
                            'value'   => $dateTime,
                            'is_date' => true,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Delete item.
     *
     * @param DeleteRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(DeleteRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $data    = [];
        $groupId = $params['group_id'];
        $userId  = $params['user_id'];

        $result = $this->repository->unmuteInGroup($context, $groupId, $userId);

        if (!$result) {
            return $this->error();
        }

        $isGroupMember = $this->memberRepository->isGroupMember($groupId, $userId);

        if ($isGroupMember) {
            $groupMember = $this->memberRepository->getGroupMember($groupId, $userId);
            $data        = new MemberItem($groupMember);
        }

        return $this->success($data, [], __p('group::phrase.member_successfully_unmuted'));
    }
}
