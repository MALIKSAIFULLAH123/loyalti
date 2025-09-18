<?php

namespace MetaFox\Quiz\Support\Browse\Scopes\Quiz;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Quiz\Models\Quiz;

class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT = Browse::VIEW_ALL;

    private string $view = self::VIEW_DEFAULT;

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

    private User $user;

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

    private bool $isOwnerView = false;

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
     * @return array<string>
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
            Browse::VIEW_SEARCH,
            Browse::VIEW_MY_PENDING,
        ];
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($this->isViewOwner()) {
            return;
        }

        $view        = $this->getView();
        $userContext = $this->getUserContext();

        switch ($view) {
            case Browse::VIEW_MY:
                $builder->where('quizzes.is_approved', 1)
                    ->where(function (Builder $whereQuery) use ($userContext) {
                        $whereQuery->where('quizzes.owner_id', '=', $userContext->entityId())
                            ->orWhere('quizzes.user_id', '=', $userContext->entityId());
                    });
                break;
            case Browse::VIEW_FRIEND:
                $builder->join('friends AS f', function (JoinClause $join) use ($userContext) {
                    $join->on('f.user_id', '=', 'quizzes.owner_id')
                        ->where([
                            ['f.owner_id', '=', $userContext->entityId()],
                            ['quizzes.is_approved', '=', 1],
                        ]);
                });
                break;
            case Browse::VIEW_PENDING:
                $builder->where('quizzes.is_approved', '<>', Quiz::IS_APPROVED);

                if ($this->getProfileId() == 0) {
                    $builder->whereColumn('quizzes.user_id', '=', 'quizzes.owner_id');
                }
                break;
            case Browse::VIEW_MY_PENDING:
                $builder->where('quizzes.is_approved', '<>', Quiz::IS_APPROVED);
                $builder->where('quizzes.user_id', $userContext->entityId());
                break;
            case Browse::VIEW_SEARCH:
                if (!$userContext->hasPermissionTo('quiz.approve')) {
                    $builder->where(function (Builder $builder) use ($userContext) {
                        $builder->where('quizzes.is_approved', Quiz::IS_APPROVED)
                            ->orWhere('quizzes.user_id', '=', $userContext->entityId());
                    });
                }

                break;
            default:
                $builder->where('quizzes.is_approved', Quiz::IS_APPROVED);
                break;
        }
    }
}
