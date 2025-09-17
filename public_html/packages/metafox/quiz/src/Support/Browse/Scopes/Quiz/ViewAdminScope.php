<?php

namespace MetaFox\Quiz\Support\Browse\Scopes\Quiz;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class ViewAdminScope extends BaseScope
{
    public const VIEW_DEFAULT = Browse::VIEW_ALL;

    private string $view = self::VIEW_DEFAULT;
    public const VIEW_APPROVED = 'approved';

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
     * @return ViewAdminScope
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
     * @return ViewAdminScope
     */
    public function setUserContext(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return Arr::pluck(self::getViewOptions(), 'value');
    }

    /**
     * @return array<int, string>
     */
    public static function getViewOptions(): array
    {
        return [
            [
                'value' => Browse::VIEW_ALL,
                'label' => __p('core::phrase.all'),
            ],
            [
                'value' => self::VIEW_APPROVED,
                'label' => __p('core::phrase.approved'),
            ],
            [
                'value' => Browse::VIEW_PENDING,
                'label' => __p('core::phrase.pending'),
            ],
            [
                'value' => Browse::VIEW_FEATURE,
                'label' => __p('core::phrase.featured'),
            ],
            [
                'value' => Browse::VIEW_SPONSOR,
                'label' => __p('core::web.sponsored'),
            ],
        ];
    }


    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $view = $this->getView();

        switch ($view) {
            case Browse::VIEW_PENDING:
                $builder->where('quizzes.is_approved', '!=', 1);
                break;
            case self::VIEW_APPROVED:
                $builder->where('quizzes.is_approved', '=', 1);
                break;
            case Browse::VIEW_SPONSOR:
                $builder->where('quizzes.is_sponsor', '=', 1);
                break;
        }
    }
}
