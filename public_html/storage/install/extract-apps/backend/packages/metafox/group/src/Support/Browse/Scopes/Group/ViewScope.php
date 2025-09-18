<?php

namespace MetaFox\Group\Support\Browse\Scopes\Group;

use Custom\Building\Support\Constants;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Log;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Support\InviteType;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT = Browse::VIEW_ALL;
    public const VIEW_JOINED  = 'joined';
    public const VIEW_INVITED = 'invited';
    public const VIEW_PROFILE = 'profile';

    public static function getMobileViewAllActionOptions(): array
    {
        $options = self::getAllowView();

        $disallowed = [
            Browse::VIEW_ALL,
            Browse::VIEW_PENDING,
            Browse::VIEW_SPONSOR,
            Browse::VIEW_SEARCH,
            Browse::VIEW_MY_PENDING,
            Browse::VIEW_FEATURE,
        ];

        return array_values(array_filter($options, function ($option) use ($disallowed) {
            return !in_array($option, $disallowed);
        }));
    }

    public static function getWebViewAllActionOptions(): array
    {
        $options = self::getAllowView();

        $disallowed = [Browse::VIEW_ALL, Browse::VIEW_SPONSOR];

        return array_values(array_filter($options, function ($option) use ($disallowed) {
            return !in_array($option, $disallowed);
        }));
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        $views = [
            Browse::VIEW_ALL,
            Browse::VIEW_MY,
            Browse::VIEW_FRIEND,
            Browse::VIEW_PENDING,
            Browse::VIEW_SPONSOR,
            self::VIEW_JOINED,
            self::VIEW_INVITED,
            Browse::VIEW_SEARCH,
            Browse::VIEW_MY_PENDING,
            Browse::VIEW_FEATURE,
            self::VIEW_PROFILE,
        ];

        try {
            app('events')->dispatch('group.view.override_options', [&$views]);
        } catch (\Throwable $exception) {
            Log::error('override group view error: ' . $exception->getMessage());
            Log::error('override group view error trace: ' . $exception->getTraceAsString());
        }

        return $views;
    }

    /**
     * @var string
     */
    private string $view = self::VIEW_DEFAULT;

    /**
     * @var User
     */
    private User $userContext;

    /**
     * @var bool
     */
    private bool $isProfile = false;
    private bool $isJoined  = true;

    /**
     * @return bool
     */
    public function isJoined(): bool
    {
        return $this->isJoined;
    }

    /**
     * @param bool $isJoined
     */
    public function setIsJoined(bool $isJoined): void
    {
        $this->isJoined = $isJoined;
    }

    /**
     * @var int
     */
    protected int $profileId = 0;

    /**
     * @return bool
     */
    public function isViewProfile(): bool
    {
        return $this->isProfile;
    }

    /**
     * @param bool $isProfile
     *
     * @return ViewScope
     */
    public function setIsViewProfile(bool $isProfile): self
    {
        $this->isProfile = $isProfile;

        return $this;
    }

    /**
     * @return User
     */
    public function getUserContext(): User
    {
        return $this->userContext;
    }

    /**
     * @param User $userContext
     *
     * @return ViewScope
     */
    public function setUserContext(User $userContext): self
    {
        $this->userContext = $userContext;

        return $this;
    }

    /**
     * @return int
     */
    public function getProfileId(): int
    {
        return $this->profileId;
    }

    /**
     * @param int $profileId
     *
     * @return ViewScope
     */
    public function setProfileId(int $profileId): self
    {
        $this->profileId = $profileId;

        return $this;
    }

    /**
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * @param string $view
     *
     * @return ViewScope
     */
    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($this->isViewProfile()) {
            return;
        }

        $view = $this->getView();

        $context = $this->getUserContext();

        $contextId = $context->entityId();

        try {
            app('events')->dispatch('group.view_scope.before_apply_eloquent_builder', [$builder, $context, $view, $model]);
        } catch (\Throwable $exception) {
            Log::error('before apply eloquent builder error: ' . $exception->getMessage());
            Log::error('before apply eloquent builder error trace: ' . $exception->getTraceAsString());
        }

        if ($view == self::VIEW_PROFILE) {
            $contextId = $this->getProfileId();
        }

        switch ($view) {
            case Browse::VIEW_MY:
                $builder->where('groups.is_approved', 1)
                    ->where('groups.user_id', '=', $contextId);
                break;
            case Browse::VIEW_FRIEND:
                $builder->join('friends AS f', function (JoinClause $join) use ($contextId) {
                    $join->on('f.user_id', '=', 'groups.user_id')
                        ->where('f.owner_id', '=', $contextId)
                        ->where('groups.is_approved', 1);
                });

                $builder->leftJoin('groups as g2', function (JoinClause $join) {
                    $join->on('g2.id', '=', 'groups.id')
                        ->where('g2.privacy_type', '=', PrivacyTypeHandler::SECRET);
                });

                $builder->leftJoin('group_members AS gm', function (JoinClause $join) use ($contextId) {
                    $join->on('gm.group_id', '=', 'groups.id')
                        ->where('gm.user_id', '=', $contextId);
                });

                if (!$this->isJoined) {
                    $builder->whereNull('gm.group_id');
                }

                $builder->where(function (Builder $q) {
                    $q->whereNull('g2.id')
                        ->orWhere([
                            ['g2.id', '!=', null],
                            ['gm.id', '!=', null],
                        ]);
                });
                break;
            case Browse::VIEW_PENDING:
                $builder->where('groups.is_approved', '!=', 1);
                break;

            case Browse::VIEW_MY_PENDING:
                $builder->where('groups.is_approved', '!=', 1);

                $builder->where('groups.user_id', $contextId);
                break;
            case self::VIEW_PROFILE:
            case self::VIEW_JOINED:
                $builder->join('group_members AS gm', function (JoinClause $join) use ($contextId) {
                    $join->on('gm.group_id', '=', 'groups.id')
                        ->where('gm.user_id', $contextId)
                        ->where('groups.is_approved', 1);
                });
                break;
            case self::VIEW_INVITED:
                $builder->whereHas('invites', function (Builder $query) use ($contextId) {
                    $query->where('owner_id', $contextId);
                    $query->where('status_id', Invite::STATUS_PENDING);
                    $query->whereIn('invite_type', [InviteType::INVITED_MEMBER, InviteType::INVITED_GENERATE_LINK]);
                })->where('groups.is_approved', 1);
                break;
            case Browse::VIEW_SEARCH:
                if (!$context->hasPermissionTo('group.approve')) {
                    $builder->where(function (Builder $builder) use ($contextId) {
                        $builder->where('groups.is_approved', 1)
                            ->orWhere('groups.user_id', '=', $contextId);
                    });
                }

                break;
            default:
                $builder->where('groups.is_approved', 1);
        }

        try {
            app('events')->dispatch('group.view_scope.after_apply_eloquent_builder', [$builder, $context, $view, $model]);
        } catch (\Throwable $exception) {
            Log::error('after apply eloquent builder error: ' . $exception->getMessage());
            Log::error('after apply eloquent builder error trace: ' . $exception->getTraceAsString());
        }
    }

//    protected function getBu
}
