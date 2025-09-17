<?php

namespace MetaFox\Event\Support\Browse\Scopes\Event;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Event\Models\Invite;
use MetaFox\Event\Models\Member;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT    = Browse::VIEW_ALL;
    public const VIEW_HOSTING    = 'hosting';
    public const VIEW_GOING      = 'going';
    public const VIEW_INTERESTED = 'interested';
    public const VIEW_RELATED    = 'related';
    public const VIEW_INVITES    = 'invites';
    public const VIEW_ON_MAP     = 'on_map';

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            Browse::VIEW_ALL,
            Browse::VIEW_MY,
            Browse::VIEW_FRIEND,
            Browse::VIEW_PENDING,
            Browse::VIEW_FEATURE,
            Browse::VIEW_SPONSOR,
            Browse::VIEW_SIMILAR,
            self::VIEW_HOSTING,
            self::VIEW_GOING,
            self::VIEW_INTERESTED,
            self::VIEW_INVITES,
            self::VIEW_RELATED,
            self::VIEW_ON_MAP,
            Browse::VIEW_SEARCH,
            Browse::VIEW_MY_PENDING,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allowViewEventAllOwnerType(): array
    {
        return [
            Browse::VIEW_MY,
            self::VIEW_HOSTING,
            self::VIEW_GOING,
            self::VIEW_INTERESTED,
            self::VIEW_INVITES,
            Browse::VIEW_MY_PENDING,
        ];
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
    private bool $isOwnerView = false;

    /**
     * @var int
     */
    protected int $profileId = 0;

    /**
     * Get the value of profileId.
     *
     * @return int
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * Set the value of profileId.
     *
     * @param int $profileId
     *
     * @return self
     */
    public function setProfileId(int $profileId)
    {
        $this->profileId = $profileId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isViewOwner(): bool
    {
        return $this->isOwnerView;
    }

    /**
     * @param bool $isViewOwner
     *
     * @return $this
     */
    public function setIsViewOwner(bool $isViewOwner): self
    {
        $this->isOwnerView = $isViewOwner;

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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($this->isViewOwner()) {
            return;
        }

        $view          = $this->getView();
        $userContext   = $this->getUserContext();
        $userContextId = $userContext->entityId();

        switch ($view) {
            case Browse::VIEW_MY:
                $builder->where('events.user_id', '=', $userContextId);
                break;
            case self::VIEW_HOSTING:
                $builder->leftJoin('event_members AS em', function (JoinClause $join) use ($userContextId) {
                    $join->on('em.event_id', '=', 'events.id')
                        ->where('em.user_id', $userContextId)
                        ->where('em.rsvp_id', Member::JOINED)
                        ->where('em.role_id', Member::ROLE_HOST);
                });
                $builder->where(function (Builder $query) use ($userContextId) {
                    $query->whereNotNull('em.user_id')
                        ->orWhere('events.user_id', '=', $userContextId);
                });
                $builder->where('events.is_approved', 1);
                break;
            case Browse::VIEW_FRIEND:
                $builder->join('friends AS f', function (JoinClause $join) use ($userContextId) {
                    $join->on('f.user_id', '=', 'events.user_id')
                        ->where('f.owner_id', '=', $userContextId)
                        ->where('events.is_approved', 1);
                });
                break;
            case Browse::VIEW_PENDING:
                $builder->where('events.is_approved', '!=', 1);
                $builder->whereColumn('events.user_id', '=', 'events.owner_id');
                break;
            case Browse::VIEW_MY_PENDING:
                /*
                 * Does not support view pending items from Group in My Pending Photos
                 */
                $builder->where('events.is_approved', '!=', 1)
                    ->where('events.user_id', $userContextId)
                    ->where('events.owner_type', $userContext->entityType());
                break;
            case self::VIEW_GOING:
                $builder->join('event_members AS em', function (JoinClause $join) use ($userContextId) {
                    $join->on('em.event_id', '=', 'events.id')
                        ->where('em.user_id', $userContextId)
                        ->where('em.rsvp_id', Member::JOINED)
                        ->where('events.is_approved', 1);
                });
                break;
            case self::VIEW_INTERESTED:
                $builder->join('event_members AS em', function (JoinClause $join) use ($userContextId) {
                    $join->on('em.event_id', '=', 'events.id')
                        ->where('em.user_id', $userContextId)
                        ->where('em.rsvp_id', Member::INTERESTED)
                        ->where('events.is_approved', 1);
                });
                break;
            case self::VIEW_RELATED:
                $builder->join('event_members AS em', function (JoinClause $join) use ($userContextId) {
                    $join->on('em.event_id', '=', 'events.id')
                        ->where('em.user_id', $userContextId)
                        ->whereIn('em.rsvp_id', [Member::JOINED, Member::INTERESTED])
                        ->where('events.is_approved', 1);
                });

                break;
            case self::VIEW_INVITES:
                $builder->whereHas('invites', function (Builder $query) use ($userContextId) {
                    $query->where('owner_id', '=', $userContextId);
                    $query->where('status_id', Invite::STATUS_PENDING);
                });

                break;
            case Browse::VIEW_SEARCH:
                if (!$userContext->hasPermissionTo('event.approve')) {
                    $builder->where(function (Builder $builder) use ($userContextId) {
                        $builder->where('events.is_approved', 1)
                            ->orWhere('events.user_id', '=', $userContextId);
                    });
                }

                break;
            case self::VIEW_ON_MAP:
                $builder->where(function (Builder $builder) use ($userContextId) {
                    $builder->where('events.is_approved', 1)
                        ->whereNotNull('events.location_name');
                });

                break;
            default:
                $builder->where('events.is_approved', 1);
        }
    }
}
