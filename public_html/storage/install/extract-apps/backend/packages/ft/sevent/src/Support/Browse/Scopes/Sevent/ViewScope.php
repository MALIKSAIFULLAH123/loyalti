<?php

namespace Foxexpert\Sevent\Support\Browse\Scopes\Sevent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\User;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 * @ignore
 * @codeCoverageIgnore
 */
class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT = Browse::VIEW_ALL;
    public const VIEW_DRAFT   = 'draft';
    public const VIEW_FAVOURITE   = 'favourite_sevents';
    public const VIEW_SIMILAR   = 'similar';
    public const VIEW_ON_MAP     = 'on_map';
    public const VIEW_ATTENDING     = 'attending';
    public const VIEW_FREE     = 'free';
    public const VIEW_PAID     = 'paid';
    public const VIEW_UPCOMING     = 'upcoming';
    public const VIEW_ONGOING     = 'ongoing';
    public const VIEW_PAST     = 'past';

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return ['sometimes', 'nullable', 'string', 'in:' . implode(',', static::getAllowView())];
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        $allowView = [
            Browse::VIEW_ALL,
            Browse::VIEW_MY,
            self::VIEW_FAVOURITE,
            self::VIEW_SIMILAR,
            Browse::VIEW_PENDING,
            Browse::VIEW_FEATURE,
            Browse::VIEW_SPONSOR,
            Browse::VIEW_SEARCH,
            self::VIEW_ATTENDING,
            Browse::VIEW_MY_PENDING,
            self::VIEW_DRAFT,
            self::VIEW_ON_MAP,

            self::VIEW_FREE,
            self::VIEW_PAID,
            self::VIEW_UPCOMING,
            self::VIEW_ONGOING,
            self::VIEW_PAST
        ];

        if (app_active('metafox/friend')) {
            $allowView[] = Browse::VIEW_FRIEND;
        }
        
        return $allowView;
    }

    /**
     * @var string
     */
    protected string $view = self::VIEW_DEFAULT;

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
        if ($this->isViewOwner()) {
            return;
        }

        $view = $this->getView();

        $userContext = $this->getUserContext();

        switch ($view) {
            case Browse::VIEW_MY:
                $builder->where('sevents.is_approved', 1)
                    ->where(function (Builder $whereQuery) use ($userContext) {
                        $whereQuery->where('sevents.owner_id', '=', $userContext->entityId())
                            ->orWhere('sevents.user_id', '=', $userContext->entityId());
                    });
                break;
                case self::VIEW_FREE:
                    $builder->Join('sevent_tickets AS f', function (JoinClause $join) use ($userContext) {
                        $join->on('f.sevent_id', '=', 'sevents.id')
                            ->where([
                                ['sevents.amount', '=', 0]
                            ]);
                    });
                    break;
                case self::VIEW_FAVOURITE:
                    $builder->Join('sevent_favourite AS f', function (JoinClause $join) use ($userContext) {
                        $join->on('f.sevent_id', '=', 'sevents.id')
                            ->where([
                                ['f.owner_id', '=', $userContext->entityId()],
                                ['sevents.is_draft', '!=', 1],
                                ['sevents.course_id', '=', 0],
                                ['sevents.is_approved', '=', 1],
                            ]);
                    });
                    break;
                case self::VIEW_ATTENDING:
                    $builder->Join('sevent_attends AS f', function (JoinClause $join) use ($userContext) {
                        $join->on('f.sevent_id', '=', 'sevents.id')
                            ->where([
                                ['f.user_id', '=', $userContext->entityId()],
                                ['f.type_id', '=', 1],
                                ['sevents.is_draft', '!=', 1],
                                ['sevents.course_id', '=', 0],
                                ['sevents.is_approved', '=', 1],
                            ]);
                    });
                break;
                case self::VIEW_SIMILAR:
                    $builder
                    ->where('sevents.is_approved', 1)
                    ->where('sevents.is_draft','!=', 1)
                    ->where('sevents.course_id','=', 0);
                        break;
                case self::VIEW_ON_MAP:
                    $builder->where(function (Builder $builder) {
                        $builder->where('sevents.is_approved', 1)
                            ->where('sevents.is_draft','!=', 1)
                            ->where('sevents.course_id','=', 0)
                            ->whereNotNull('sevents.location_latitude');
                    });
    
                    break;
            case Browse::VIEW_FRIEND:
                if (app_active('metafox/friend')) {
                    $builder->join('friends AS f', function (JoinClause $join) use ($userContext) {
                        $join->on('f.user_id', '=', 'sevents.owner_id')
                            ->where([
                                ['f.owner_id', '=', $userContext->entityId()],
                                ['sevents.is_draft', '!=', 1],
                                ['sevents.course_id', '=', 0],
                                ['sevents.is_approved', '=', 1],
                            ]);
                    });
                }
                break;
            case Browse::VIEW_PENDING:
                $builder->where('sevents.is_approved', '!=', 1)
                    ->where('sevents.is_draft', 0)
                    ->where('sevents.course_id','=', 0);

                break;
            case Browse::VIEW_MY_PENDING:
                $builder->where('sevents.is_draft', 0)
                    ->where('sevents.course_id','=', 0)
                    ->whereNot('sevents.is_approved', 1)
                    ->where('sevents.user_id', $userContext->entityId());
                break;

            case self::VIEW_DRAFT:
                $builder->where([
                    ['sevents.is_draft', '=', 1],
                    ['sevents.course_id', '=', 0],
                    ['sevents.user_id', '=', $userContext->entityId()],
                ]);
                break;
            break;
            case Browse::VIEW_SEARCH:
                if (!$userContext->hasPermissionTo('sevent.approve')) {
                    $builder->where(function (Builder $subQuery) use ($userContext) {
                        $subQuery->where('sevents.is_approved', '=', 1)
                            ->orWhere('sevents.user_id', '=', $userContext->entityId());
                    });
                }

                if (!$userContext->hasPermissionTo('sevent.moderate')) {
                    $builder->where(function (Builder $subQuery) use ($userContext) {
                        $subQuery->where('sevents.is_draft', '=', 0)
                             ->where('sevents.course_id','=', 0)
                            ->orWhere('sevents.user_id', '=', $userContext->entityId());
                    });
                }

                break;
            default:
                $builder->where('sevents.is_approved', '=', 1)
                    ->where('sevents.is_draft', '=', 0);
        }
    }
}
