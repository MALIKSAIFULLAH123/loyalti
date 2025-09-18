<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Request;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface RequestRepositoryInterface.
 *
 * @mixin BaseRepository
 * @method Request getModel()
 * @method Request find($id, $columns = ['*'])
 * @mixin UserMorphTrait
 */
interface RequestRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewRequests(User $context, array $attributes): Paginator;

    /**
     * @param array $attributes
     *
     * @return Builder
     */
    public function buildViewRequestsQuery(array $attributes): Builder;

    /**
     * @param  User                   $context
     * @param  int                    $groupId
     * @param  int                    $userId
     * @return Request
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws ValidatorException
     * @deprecated
     */
    public function acceptMemberRequest(User $context, int $groupId, int $userId): Request;

    /**
     * @param  User    $context
     * @param  Request $request
     * @return Request
     */
    public function acceptRequest(User $context, Request $request): Request;

    /**
     * @param  User    $context
     * @param  Request $request
     * @param  array   $params
     * @return Request
     */
    public function declineRequest(User $context, Request $request, array $params = []): Request;

    /**
     * @param User $context
     * @param int  $groupId
     * @param int  $userId
     *
     * @return Request
     * @throws ValidationException
     * @throws AuthorizationException
     * @deprecated
     */
    public function denyMemberRequest(User $context, int $groupId, int $userId): Request;

    /**
     * @param User $context
     * @param int  $groupId
     *
     * @return bool
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function cancelRequest(User $context, int $groupId): bool;

    /**
     * @param int $userId
     * @param int $groupId
     *
     * @return Model|null
     */
    public function getRequestByUserGroupId(int $userId, int $groupId, int $statusId): ?Model;

    /**
     * @param int  $groupId
     * @param User $user
     *
     * @return void
     */
    public function handelRequestJoinGroup(int $groupId, User $user): void;

    /**
     * @param string $notificationType
     * @param int    $itemId
     * @param string $itemType
     *
     * @return void
     */
    public function removeNotificationForPendingRequest(string $notificationType, int $itemId, string $itemType): void;

    /**
     * @param Group $group
     *
     * @return Builder
     */
    public function getBuilderPendingRequests(Group $group): Builder;
}
