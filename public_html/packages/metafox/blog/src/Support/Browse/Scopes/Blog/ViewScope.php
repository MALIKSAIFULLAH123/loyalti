<?php

namespace MetaFox\Blog\Support\Browse\Scopes\Blog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\User;
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
            Browse::VIEW_PENDING,
            Browse::VIEW_FEATURE,
            Browse::VIEW_SPONSOR,
            Browse::VIEW_SEARCH,
            Browse::VIEW_MY_PENDING,
            self::VIEW_DRAFT,
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
                $builder->where('blogs.is_approved', 1)
                    ->where('blogs.is_draft', 0)
                    ->where(function (Builder $whereQuery) use ($userContext) {
                        $whereQuery->where('blogs.owner_id', '=', $userContext->entityId())
                            ->orWhere('blogs.user_id', '=', $userContext->entityId());
                    });
                break;
            case Browse::VIEW_FRIEND:
                if (app_active('metafox/friend')) {
                    $builder->join('friends AS f', function (JoinClause $join) use ($userContext) {
                        $join->on('f.user_id', '=', 'blogs.owner_id')
                            ->where([
                                ['f.owner_id', '=', $userContext->entityId()],
                                ['blogs.is_draft', '!=', 1],
                                ['blogs.is_approved', '=', 1],
                            ]);
                    });
                }
                break;
            case Browse::VIEW_PENDING:
                $builder->where('blogs.is_approved', '!=', 1)
                    ->where('blogs.is_draft', 0);

                break;
            case Browse::VIEW_MY_PENDING:
                $builder->where('blogs.is_draft', 0)
                    ->whereNot('blogs.is_approved', 1)
                    ->where('blogs.user_id', $userContext->entityId());
                break;

            case self::VIEW_DRAFT:
                $builder->where([
                    ['blogs.is_draft', '=', 1],
                    ['blogs.user_id', '=', $userContext->entityId()],
                ]);
                break;
            case Browse::VIEW_SEARCH:
                if (!$userContext->hasPermissionTo('blog.approve')) {
                    $builder->where(function (Builder $subQuery) use ($userContext) {
                        $subQuery->where('blogs.is_approved', '=', 1)
                            ->orWhere('blogs.user_id', '=', $userContext->entityId());
                    });
                }

                if (!$userContext->hasPermissionTo('blog.moderate')) {
                    $builder->where(function (Builder $subQuery) use ($userContext) {
                        $subQuery->where('blogs.is_draft', '=', 0)
                            ->orWhere('blogs.user_id', '=', $userContext->entityId());
                    });
                }

                break;
            default:
                $builder->where('blogs.is_approved', '=', 1)
                    ->where('blogs.is_draft', '=', 0);
        }
    }
}
