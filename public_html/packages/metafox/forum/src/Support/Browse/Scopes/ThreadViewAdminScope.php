<?php

namespace MetaFox\Forum\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class ThreadViewAdminScope extends BaseScope
{
    public const VIEW_WIKI     = 'wiki';
    public const VIEW_DEFAULT  = Browse::VIEW_ALL;
    public const VIEW_CLOSED   = 'closed';
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
            [
                'value' => self::VIEW_WIKI,
                'label' => __p('forum::web.wiki'),
            ],
            [
                'value' => self::VIEW_CLOSED,
                'label' => __p('core::web.closed'),
            ],
            [
                'value' => Browse::VIEW_SPONSOR,
                'label' => __p('core::web.sponsored'),
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

    public function apply(Builder $builder, Model $model)
    {
        $view = $this->view;

        switch ($view) {
            case self::VIEW_APPROVED:
                $builder->where('forum_threads.is_approved', '=', 1);
                break;
            case Browse::VIEW_PENDING:
                $builder->where('forum_threads.is_approved', '=', 0);
                break;
            case self::VIEW_WIKI:
                $builder->where([
                    'forum_threads.is_approved' => 1,
                    'forum_threads.is_wiki'     => 1,
                ]);
                break;
            case self::VIEW_CLOSED:
                $builder->where([
                    'forum_threads.is_approved' => 1,
                    'forum_threads.is_closed'   => 1,
                ]);
                break;
            case Browse::VIEW_SPONSOR:
                $builder->where('forum_threads.is_sponsor', '=', 1);
                break;
        }
    }
}
