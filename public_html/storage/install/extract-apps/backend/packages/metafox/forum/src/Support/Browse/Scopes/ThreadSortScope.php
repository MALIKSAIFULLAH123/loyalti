<?php

namespace MetaFox\Forum\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasAlphabetSort;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Traits\AlphabetSortTrait;

/**
 * Class SortScope.
 */
class ThreadSortScope extends SortScope implements HasAlphabetSort
{
    use AlphabetSortTrait;

    public const SORT_DEFAULT              = Browse::SORT_RECENT;
    public const SORT_TYPE_DEFAULT         = Browse::SORT_TYPE_DESC;
    public const SORT_RECENT_POST          = 'recent_post';
    public const SORT_LATEST_DISCUSSED     = 'latest_discussed';
    public const SORT_LATEST_POSTS         = 'latest_post';

    /**
     * This sort groups will sort threads by sticked desc first.
     */
    public const SORT_TITLE                = 'title';
    public const SORT_FIRST_REPLY          = 'first_reply';
    public const SORT_LAST_POST            = 'last_post';
    public const SORT_DISCUSSED            = 'discussed';
    public const SORT_REPLIES              = 'replies';

    /**
     * @var string|null
     */
    protected $view;

    /**
     * @var string|null
     */
    protected $sort;

    /**
     * @var string|null
     */
    protected $sortType;

    /**
     * @param  string $view
     * @return $this
     */
    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getView(): ?string
    {
        return $this->view;
    }

    /**
     * @param  string $sort
     * @return $this
     */
    public function setSort(string $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @param  string $sortType
     * @return $this
     */
    public function setSortType(string $sortType): self
    {
        $this->sortType = $sortType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSortType(): string
    {
        return $this->sortType;
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return array_merge(parent::getAllowSort(), [
            self::SORT_FIRST_REPLY,
            self::SORT_LAST_POST,
            self::SORT_TITLE,
            self::SORT_RECENT_POST,
            self::SORT_DISCUSSED,
            self::SORT_REPLIES,
            self::SORT_LATEST_DISCUSSED,
        ]);
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function apply(Builder $builder, Model $model)
    {
        $table    = $model->getTable();
        $view     = $this->getView();
        $sortType = $this->getSortType();
        $sort     = $this->getSort();

        switch ($view) {
            case ThreadViewScope::VIEW_HISTORY:
                $builder->orderBy($this->alias('lr', 'updated_at'), $sortType);
                break;
            case Browse::VIEW_LATEST:
                $builder->orderBy($this->alias($table, 'created_at'), $sortType)
                    ->orderBy($this->alias($table, $model->getKeyName()), $sortType);
                break;
            default:
                switch ($sort) {
                    case self::SORT_FIRST_REPLY:
                        $builder->leftJoin('forum_posts', function (JoinClause $joinClause) {
                            $joinClause->on('forum_posts.id', '=', 'forum_threads.first_post_id');
                        })
                            ->orderByDesc($this->alias($table, 'is_sticked'))
                            ->orderBy(DB::raw('CASE WHEN forum_posts.created_at IS NULL THEN 1 ELSE 2 END'), 'desc')
                            ->orderBy('forum_posts.created_at', $sortType)
                            ->orderBy($this->alias($table, 'created_at'), $sortType)
                            ->orderBy($this->alias($table, $model->getKeyName()), $sortType);
                        break;
                    case Browse::SORT_MOST_LIKED:
                    case Browse::SORT_MOST_DISCUSSED:
                        /*
                         * Nothing to do
                         */
                        break;
                    case self::SORT_LATEST_DISCUSSED:
                        $this->setSort(Browse::SORT_RECENT);
                        break;
                    case self::SORT_DISCUSSED:
                        $this->orderByStickedDesc($builder, $table);
                        $this->setSort(Browse::SORT_RECENT);
                        break;
                    case self::SORT_TITLE:
                        $this->orderByStickedDesc($builder, $table);
                        $this->setSort($sortType == Browse::SORT_TYPE_DESC ? Browse::SORT_Z_TO_A : Browse::SORT_A_TO_Z);
                        break;
                    case self::SORT_REPLIES:
                        $this->orderByStickedDesc($builder, $table);
                        $this->setSort(Browse::SORT_MOST_DISCUSSED);
                        break;
                    case self::SORT_LATEST_POSTS:
                    case self::SORT_RECENT_POST:
                        $this->buildLatestPosts($builder, $model);
                        break;
                    case self::SORT_LAST_POST:
                        $builder->orderByDesc($this->alias($table, 'is_sticked'));
                        $this->buildLatestPosts($builder, $model, $sortType);
                        break;
                    default:
                        $this->orderByStickedDesc($builder, $table);

                        if ($view != Browse::VIEW_SEARCH) {
                            $builder->orderBy($this->alias($table, 'id'), $sortType);
                        }
                }

                break;
        }

        parent::apply($builder, $model);
    }

    public function orderByStickedDesc(Builder $builder, string $table)
    {
        $builder->orderByDesc($this->alias($table, 'is_sticked'));
    }

    protected function buildLatestPosts(Builder $builder, Model $model, string $sortType = Browse::SORT_TYPE_DESC): void
    {
        $table    = $model->getTable();

        $builder
            ->select(['forum_threads.*'])
            ->selectRaw(DB::raw('(CASE WHEN forum_posts.created_at IS NULL THEN forum_threads.created_at ELSE forum_posts.created_at END) as ordered_date'))
            ->leftJoin('forum_posts', function (JoinClause $joinClause) {
                $joinClause->on('forum_posts.id', '=', 'forum_threads.last_post_id');
            })
            ->orderBy('ordered_date', $sortType)
            ->orderBy($this->alias($table, 'created_at'), $sortType)
            ->orderBy($this->alias($table, $model->getKeyName()), $sortType);
    }

    protected function buildLastReplies(Builder $builder, Model $model, bool $isOrderedBySticked = false): void
    {
        $table    = $model->getTable();
        $sortType = $this->getSortType();

        $builder->leftJoin('forum_posts', function (JoinClause $joinClause) {
            $joinClause->on('forum_posts.id', '=', 'forum_threads.last_post_id');
        });

        if ($isOrderedBySticked) {
            $builder->orderByDesc($this->alias($table, 'is_sticked'));
        }

        $builder->orderBy(DB::raw('CASE WHEN forum_posts.created_at IS NULL THEN 1 ELSE 2 END'), 'desc')
            ->orderBy('forum_posts.created_at', $sortType)
            ->orderBy($this->alias($table, 'created_at'), $sortType)
            ->orderBy($this->alias($table, $model->getKeyName()), $sortType);
    }

    public function getAlphabetSortColumn(): string
    {
        return 'title';
    }
}
