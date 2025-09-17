<?php

namespace MetaFox\Forum\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class ThreadViewScope extends BaseScope
{
    public const VIEW_SUBSCRIBED   = 'subscribed';
    public const VIEW_HISTORY      = 'history';
    public const VIEW_WIKI         = 'wiki';
    public const VIEW_MERGED       = 'merged';
    public const VIEW_DEFAULT      = Browse::VIEW_ALL;
    public const VIEW_LATEST_POSTS = 'latest_posts';

    /**
     * @var string
     */
    protected $view;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var User
     */
    protected $owner;

    /**
     * @var int
     */
    protected $profileId = 0;

    /**
     * @return array
     */
    public static function getAllowView(): array
    {
        return [
            Browse::VIEW_LATEST,
            Browse::VIEW_ALL,
            Browse::VIEW_MY,
            Browse::VIEW_PENDING,
            self::VIEW_HISTORY,
            self::VIEW_SUBSCRIBED,
            self::VIEW_WIKI,
            self::VIEW_MERGED,
            Browse::VIEW_SEARCH,
            Browse::VIEW_MY_PENDING,
            self::VIEW_LATEST_POSTS,
            Browse::VIEW_SPONSOR,
        ];
    }

    /**
     * @param  User  $owner
     * @return $this
     */
    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @param  int   $id
     * @return $this
     */
    public function setProfileId(int $id): self
    {
        $this->profileId = $id;

        return $this;
    }

    /**
     * @param  string|null $view
     * @return $this
     */
    public function setView(?string $view = null): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @param  User  $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function apply(Builder $builder, Model $model)
    {
        $view      = $this->view;
        $context   = $this->user;
        $profileId = $this->profileId;
        $owner     = $this->owner;

        if ($profileId > 0 && $profileId != $context->entityId()) {
            $builder->where('forum_threads.is_approved', '=', 1);
        }

        if (null !== $owner
            && $owner->entityId() != $context->ownerId()) {
            $builder->where('forum_threads.is_approved', '=', 1);
        }

        switch ($view) {
            case Browse::VIEW_MY:
                $builder->where('forum_threads.is_approved', 1)
                    ->where(function (Builder $whereQuery) use ($context) {
                        $whereQuery->where('forum_threads.owner_id', '=', $context->entityId())
                            ->orWhere('forum_threads.user_id', '=', $context->entityId());
                    });
                break;
            case Browse::VIEW_MY_PENDING:
                $builder->where('forum_threads.is_approved', 0)
                    ->where(function (Builder $whereQuery) use ($context) {
                        $whereQuery->where('forum_threads.owner_id', '=', $context->entityId())
                            ->orWhere('forum_threads.user_id', '=', $context->entityId());
                    });
                break;
            case Browse::VIEW_PENDING:
                $builder->where('forum_threads.is_approved', '=', 0);
                if ($profileId == $context->entityId()) {
                    $builder->where('forum_threads.user_id', $profileId);
                }

                if ($profileId == 0) {
                    $builder->whereColumn('forum_threads.user_id', '=', 'forum_threads.owner_id');
                }
                break;
            case self::VIEW_HISTORY:
                if (!$context->hasPermissionTo('forum_thread.approve')) {
                    $builder->where('forum_threads.is_approved', '=', 1);
                }

                $builder->join('forum_thread_last_read as lr', function (JoinClause $clause) use ($context) {
                    $clause->on('lr.thread_id', '=', 'forum_threads.id')
                        ->where('lr.user_id', '=', $context->entityId());
                });
                break;
            case self::VIEW_SUBSCRIBED:
                $builder->where('forum_threads.is_approved', 1);
                $builder->join('forum_thread_subscribes as ls', function (JoinClause $clause) use ($context) {
                    $clause->on('ls.item_id', '=', 'forum_threads.id')
                        ->where('ls.user_id', '=', $context->entityId());
                });
                break;
            case self::VIEW_WIKI:
                $builder->where([
                    'forum_threads.is_approved' => 1,
                    'forum_threads.is_wiki'     => 1,
                ]);
                break;
            case self::VIEW_MERGED:
                if (!$context->hasPermissionTo('forum_thread.moderate')) {
                    $builder->where([
                        ['forum_threads.owner_id', '=', $context->entityId(), 'or'],
                        ['forum_threads.user_id', '=', $context->entityId(), 'or'],
                    ]);
                }

                break;
            case Browse::VIEW_SEARCH:
                if (!$context->hasPermissionTo('forum_thread.approve')) {
                    $builder->where(function (Builder $builder) use ($context) {
                        $builder->where('forum_threads.is_approved', '=', 1)
                            ->orWhere('forum_threads.user_id', '=', $context->entityId());
                    });
                }

                break;
            case self::VIEW_LATEST_POSTS:
                $this->buildDefaultCondition($builder);
                break;
            default:
                $this->buildDefaultCondition($builder);
        }
    }

    protected function buildDefaultCondition(Builder $builder): void
    {
        $builder->where([
            'forum_threads.is_approved' => 1,
            'forum_threads.is_wiki'     => 0,
        ]);
    }
}
