<?php

namespace MetaFox\Platform\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\DbTableHelper;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class PrivacyScope.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class PrivacyScope extends BaseScope
{
    /**
     * @var int
     */
    protected int $userId = 0;

    /**
     * @var int|null
     */
    protected ?int $ownerId = null;

    /**
     * @var string|null
     */
    protected ?string $privacyColumn = null;

    /**
     * @var string|null
     */
    protected ?string $moderationPermissionName = null;

    /**
     * @var array|null
     */
    protected ?array $moderationUserRoles = null;

    /**
     * @var bool
     */
    protected bool $hasUserBlock = false;

    public function setPrivacyColumn(string $column): void
    {
        $this->privacyColumn = $column;
    }

    public function getPrivacyColumn(): string
    {
        if (null === $this->privacyColumn) {
            $this->privacyColumn = DbTableHelper::PRIVACY_COLUMN;
        }

        return $this->privacyColumn;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    public function setOwnerId(int $ownerId): self
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setModerationPermissionName(string $name): self
    {
        $this->moderationPermissionName = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getModerationPermissionName(): ?string
    {
        return $this->moderationPermissionName;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setModerationUserRoles(array $roles): self
    {
        $this->moderationUserRoles = $roles;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getModerationUserRoles(): ?array
    {
        return $this->moderationUserRoles;
    }

    public function setHasUserBlock(bool $hasUserBlock): self
    {
        $this->hasUserBlock = $hasUserBlock;

        return $this;
    }

    public function getHasUserBlock(): bool
    {
        return $this->hasUserBlock;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $this->addPrivacyScope($builder, $model);

        $this->addBlockedScope($builder, $model);

        $ownerId = $this->getOwnerId();

        $resourceOwnerColumn = $model->getTable() . '.owner_id';

        if (null !== $ownerId) {
            $builder->where($resourceOwnerColumn, $ownerId);
        }

        $hookParams = [
            'user_id'                    => $this->getUserId(),
            'owner_id'                   => $this->getOwnerId(),
            'moderation_permission_name' => $this->getModerationPermissionName(),
            'moderation_user_roles'      => $this->getModerationUserRoles(),
            'has_user_block'             => $this->getHasUserBlock(),
        ];

        app('events')->dispatch('platform.privacy_scope.after_apply', [$builder, $model, $hookParams]);
    }

    protected function isFriendOfFriendScope(): bool
    {
        $userId = $this->getUserId();

        $ownerId = $this->getOwnerId();

        if (!app_active('metafox/friend')) {
            return false;
        }

        if ($userId === MetaFoxConstant::GUEST_USER_ID) {
            return false;
        }

        if (null === $ownerId) {
            return false;
        }

        if ($userId == $ownerId) {
            return false;
        }

        $context = UserEntity::getById($userId)->detail;

        $owner = UserEntity::getById($ownerId)->detail;

        return (bool) app('events')->dispatch('friend.is_friend_of_friend', [$context->id, $owner->id], true);
    }

    protected function addPrivacyScope(Builder $builder, Model $model): void
    {
        $streamTable = null;

        // Support models which not integrated privacy to core_privacy_streams but define privacy_stream in its
        if (method_exists($model, 'privacyStreams')) {
            $streamTable = $model->privacyStreams()->getRelated()->getTable();
        }

        if (null === $streamTable) {
            abort(400, __p('validation.this_model_not_support_stream_resource'));
        }

        if ($this->hasResourceModeration()) {
            return;
        }

        $table = $model->getTable();

        $primaryKey = sprintf('%s.%s', $table, $model->getKeyName());

        $streamTable = $model->privacyStreams()->getRelated()->getTable();

        $streamTableAs = sprintf('%s AS stream', $streamTable);

        $subQuery = DB::table($streamTableAs, 'stream')
            ->select(['stream.item_id'])
            ->distinct('stream.item_id');

        if ($this->hasPrivacyMemberScope()) {
            $subQuery->join('core_privacy_members AS member', function (JoinClause $join) {
                $join->on('stream.privacy_id', '=', 'member.privacy_id')
                    ->where('member.privacy_id', '<>', MetaFoxPrivacy::NETWORK_FRIEND_OF_FRIENDS_ID)
                    ->where('member.user_id', '=', $this->getUserId());
            });
        }

        $builder->joinSub($subQuery, 'item', function (JoinClause $joinClause) use ($primaryKey) {
            $joinClause->on('item.item_id', '=', $primaryKey);
        });
    }

    protected function hasResourceModeration(): bool
    {
        $userId = $this->getUserId();

        if (!$userId) {
            return false;
        }

        $ownerId = $this->getOwnerId();

        $user = UserEntity::getById($userId)->detail;

        if (null !== $ownerId) {
            $owner = UserEntity::getById($ownerId)->detail;

            if (method_exists($owner, 'hasResourceModeration')) {
                if ($owner->hasResourceModeration($user)) {
                    return true;
                }
            }
        }

        $moderatePermissionName = $this->getModerationPermissionName();

        if (null !== $moderatePermissionName) {
            return $user->hasPermissionTo($moderatePermissionName);
        }

        $moderateUserRoles = $this->getModerationUserRoles();

        if (is_array($moderateUserRoles)) {
            return $user->hasRole($moderateUserRoles);
        }

        return false;
    }

    protected function addBlockedScope(Builder $builder, Model $model): void
    {
        if (!$this->getHasUserBlock()) {
            return;
        }

        $table = $model->getTable();

        $hasColumn = Cache::rememberForever("$table.owner_id", function () use ($table) {
            return Schema::hasColumn($table, 'owner_id');
        });

        $blockedScope = new BlockedScope();
        $blockedScope->setContextId($this->getUserId())
            ->setTable($table)
            ->setPrimaryKey('user_id');

        if ($hasColumn) {
            $blockedScope->setSecondKey('owner_id');
        }

        $builder->addScope($blockedScope);
    }

    protected function hasPrivacyMemberScope(): bool
    {
        return true;
    }

    /**
     * @deprecated
     */
    protected function addPrivacyMemberScope(Builder $builder, Model $model): void
    {
        if ($this->hasResourceModeration()) {
            return;
        }

        if (!$this->hasPrivacyMemberScope()) {
            return;
        }

        $builder->join('core_privacy_members AS member', function (JoinClause $join) {
            $join->on('stream.privacy_id', '=', 'member.privacy_id')
                ->where('member.user_id', '=', $this->getUserId());
        });
    }
}
