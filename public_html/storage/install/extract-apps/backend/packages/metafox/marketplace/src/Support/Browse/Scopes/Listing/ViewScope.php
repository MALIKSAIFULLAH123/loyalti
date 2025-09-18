<?php

namespace MetaFox\Marketplace\Support\Browse\Scopes\Listing;

use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use MetaFox\Marketplace\Policies\ListingPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class ViewScope.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT   = Browse::VIEW_ALL;
    public const VIEW_INVITE    = 'invite';
    public const VIEW_HISTORY   = 'history';
    public const VIEW_EXPIRE    = 'expire';
    public const VIEW_ALIVE     = 'alive';
    public const VIEW_PROFILE   = 'profile';

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
            self::VIEW_INVITE,
            self::VIEW_HISTORY,
            self::VIEW_EXPIRE,
            Browse::VIEW_SEARCH,
            Browse::VIEW_MY_PENDING,
            self::VIEW_ALIVE,
            self::VIEW_PROFILE,
        ];
    }

    /**
     * @var string
     */
    private string $view = self::VIEW_DEFAULT;

    /**
     * @var User
     */
    protected User $user;

    /**
     * @var bool
     */
    protected bool $isViewOwner = false;

    /**
     * @var int
     */
    protected int $profileId = 0;

    /**
     * @return bool
     */
    public function isViewOwner(): bool
    {
        return $this->isViewOwner;
    }

    /**
     * @param bool $isViewOwner
     *
     * @return ViewScope
     */
    public function setIsViewOwner(bool $isViewOwner): self
    {
        $this->isViewOwner = $isViewOwner;

        return $this;
    }

    /**
     * @return User
     */
    public function getUserContext(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return ViewScope
     */
    public function setUserContext(User $user): self
    {
        $this->user = $user;

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
     * @param  int   $profileId
     * @return $this
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
     */
    public function apply(Builder $builder, Model $model)
    {
        $view = $this->getView();

        $context = $this->getUserContext();

        $profileId = $this->getProfileId();

        $this->viewAvailableItems($builder);

        if (!$this->checkViewAll()) {
            $buildExpired = $view == self::VIEW_EXPIRE;

            match ($buildExpired) {
                true  => $this->buildExpired($builder),
                false => $this->buildAlive($builder),
            };
        }

        if (!in_array($view, [Browse::VIEW_PENDING, Browse::VIEW_MY_PENDING, Browse::VIEW_SEARCH])) {
            $builder->where('marketplace_listings.is_approved', '=', 1);
        }

        if ($this->isViewOwner()) {
            return;
        }

        switch ($view) {
            case self::VIEW_PROFILE:
            case Browse::VIEW_MY:
                $builder->where(function (Builder $whereQuery) use ($context) {
                    $whereQuery->where('marketplace_listings.owner_id', '=', $context->entityId())
                        ->orWhere('marketplace_listings.user_id', '=', $context->entityId());
                })->where('marketplace_listings.is_approved', '=', 1);
                break;
            case Browse::VIEW_FRIEND:
                $builder->join('friends AS f', function (JoinClause $join) use ($context) {
                    $join->on('f.user_id', '=', 'marketplace_listings.owner_id')
                        ->where([
                            ['f.owner_id', '=', $context->entityId()],
                            ['marketplace_listings.is_approved', '=', 1],
                        ]);
                });
                break;
            case Browse::VIEW_PENDING:
                $builder->where('marketplace_listings.is_approved', '=', 0);
                break;
            case Browse::VIEW_MY_PENDING:
                $builder->whereNot('marketplace_listings.is_approved', 1)
                    ->where('marketplace_listings.user_id', '=', $context->entityId());

                break;
            case self::VIEW_INVITE:
                $builder->join('marketplace_invites', function (JoinClause $join) use ($context) {
                    $join->on('marketplace_invites.listing_id', '=', 'marketplace_listings.id')
                        ->where([
                            ['marketplace_invites.owner_id', '=', $context->entityId()],
                            ['marketplace_listings.is_approved', '=', 1],
                        ]);
                });
                break;
            case self::VIEW_HISTORY:
                $builder->join('marketplace_listing_histories', function (JoinClause $join) use ($context) {
                    $join->on('marketplace_listing_histories.listing_id', '=', 'marketplace_listings.id')
                        ->where([
                            ['marketplace_listing_histories.user_id', '=', $context->entityId()],
                            ['marketplace_listings.is_approved', '=', 1],
                        ])
                        ->whereNotNull('marketplace_listing_histories.visited_at');
                })
                    ->orderByDesc('marketplace_listing_histories.visited_at');
                break;
            case Browse::VIEW_SEARCH:
                $this->buildViewSearch($builder);
                break;
            case self::VIEW_EXPIRE:
                if ($profileId == $context->entityId()) {
                    $builder->where('marketplace_listings.user_id', '=', $profileId);
                }

                break;
        }
    }

    protected function buildViewSearch(Builder $builder): void
    {
        $context = $this->getUserContext();

        if (!$context->hasPermissionTo('marketplace.moderate')) {
            $builder->where(function (Builder $builder) use ($context) {
                $builder->where('marketplace_listings.is_sold', '=', 0)
                    ->orWhere('marketplace_listings.user_id', '=', $context->entityId());
            });
        }

        $hasApprove                       = $context->hasPermissionTo('marketplace.approve');
        $hasModerateViewExpiredPermission = policy_check(ListingPolicy::class, 'hasModerateViewExpiredPermission', $context);

        if ($hasApprove && $hasModerateViewExpiredPermission) {
            return;
        }

        if ($hasApprove && !$hasModerateViewExpiredPermission) {
            $builder->where(function (Builder $builder) {
                $this->buildAliveOrMyExpired($builder);
            });

            return;
        }

        if (!$hasModerateViewExpiredPermission) {
            $builder->where(function (Builder $builder) use ($context) {
                $builder->where(function (Builder $builder) {
                    $builder->where('marketplace_listings.is_approved', '=', 1)
                        ->where(function (Builder $builder) {
                            $this->buildAliveOrMyExpired($builder);
                        });
                })->orWhere('marketplace_listings.user_id', '=', $context->entityId());
            });

            return;
        }

        $builder->where(function (Builder $builder) use ($context) {
            $builder->where('marketplace_listings.is_approved', '=', 1)
                ->orWhere('marketplace_listings.user_id', '=', $context->entityId());
        });
    }

    protected function buildAliveOrMyExpired(Builder $builder): void
    {
        $context = $this->getUserContext();

        $builder->where(function (Builder $builder) use ($context) {
            $builder->where(function (Builder $builder) {
                $builder->where('marketplace_listings.start_expired_at', '>', Carbon::now()->timestamp)
                    ->orWhere('marketplace_listings.start_expired_at', '=', 0);
            })->orWhere(function (Builder $builder) use ($context) {
                $builder->where('marketplace_listings.start_expired_at', '<=', Carbon::now()->timestamp)
                    ->where('marketplace_listings.start_expired_at', '>', 0)
                    ->where('marketplace_listings.user_id', '=', $context->entityId());
            });
        });
    }

    protected function checkViewAll(): bool
    {
        $view = $this->getView();

        $context = $this->getUserContext();

        $profileId = $this->getProfileId();

        if (in_array($view, [Browse::VIEW_SEARCH, Browse::VIEW_PENDING, Browse::VIEW_MY_PENDING, self::VIEW_PROFILE])) {
            return true;
        }

        if ($view !== self::VIEW_DEFAULT) {
            return false;
        }

        if ($profileId == 0) {
            return false;
        }

        $owner = UserEntity::getById($profileId)->detail;

        if (!policy_check(ListingPolicy::class, 'viewExpire', $context, $owner, $profileId)) {
            return false;
        }

        return true;
    }

    protected function buildExpired(BuilderContract $builder): void
    {
        $builder->where(function (BuilderContract $builder) {
            $builder->where('marketplace_listings.start_expired_at', '<=', Carbon::now()->timestamp)
                ->where('marketplace_listings.start_expired_at', '>', 0);
        });
    }

    protected function buildAlive(BuilderContract $builder): void
    {
        $view = $this->getView();

        if ($view == self::VIEW_EXPIRE) {
            return;
        }

        $builder->where(function (BuilderContract $builder) {
            $builder->where('marketplace_listings.start_expired_at', '>', Carbon::now()->timestamp)
                ->orWhere('marketplace_listings.start_expired_at', '=', 0);
        });
    }

    protected function viewAvailableItems(BuilderContract $builder): void
    {
        $view = $this->getView();

        if (in_array($view, [
            Browse::VIEW_MY,
            Browse::VIEW_FEATURE,
            Browse::VIEW_SPONSOR,
            self::VIEW_EXPIRE,
            self::VIEW_HISTORY,
            Browse::VIEW_SEARCH, ])) {
            return;
        }

        $builder->where('marketplace_listings.is_sold', '=', 0);
    }
}
