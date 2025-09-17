<?php

namespace MetaFox\Forum\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class PostViewAdminScope extends BaseScope
{
    public const VIEW_DEFAULT  = Browse::VIEW_ALL;
    public const VIEW_APPROVED = 'approved';

    /**
     * @var string
     */
    protected $view;

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
        ];
    }


    /**
     * @param string|null $view
     * @return $this
     */
    public function setView(?string $view = null): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function apply(Builder $builder, Model $model)
    {
        $view = $this->view;

        switch ($view) {
            case Browse::VIEW_PENDING:
                $builder->where('forum_posts.is_approved', '=', 0);
                break;
            case self::VIEW_APPROVED:
                $builder->where('forum_posts.is_approved', '=', 1);
                break;
        }
    }
}
