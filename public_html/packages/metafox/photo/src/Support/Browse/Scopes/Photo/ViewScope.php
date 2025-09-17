<?php

namespace MetaFox\Photo\Support\Browse\Scopes\Photo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Photo\Models\Photo;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT             = Browse::VIEW_ALL;
    public const VIEW_NO_ALBUM            = 'no_album';
    public const VIEW_TAG_WALL            = 'tag_wall';

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            Browse::VIEW_LATEST,
            Browse::VIEW_ALL,
            Browse::VIEW_MY,
            Browse::VIEW_FRIEND,
            Browse::VIEW_PENDING,
            Browse::VIEW_FEATURE,
            Browse::VIEW_SPONSOR,
            self::VIEW_NO_ALBUM,
            self::VIEW_TAG_WALL,
            Browse::VIEW_MY_PENDING,
            Browse::VIEW_SEARCH,
        ];
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
     * @var User
     */
    protected User $owner;

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

    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $userContext = $this->getUserContext();

        $view = $this->getView();

        if ($view == self::VIEW_TAG_WALL) {
            $this->handleJoinForMyTag($builder, $table);

            $builder->where($this->alias($table, 'is_approved'), 1)
                ->where(function (Builder $builder) use ($table) {
                    $builder->where($this->alias($table, 'owner_id'), '=', $this->getProfileId())
                        ->where($this->alias($table, 'user_id'), '<>', $this->getProfileId());

                    $this->handleQueryForMyTag($builder, $table);
                });

            return;
        }

        if ($this->isViewOwner()) {
            if ($view == self::VIEW_NO_ALBUM) {
                $this->buildQueryViewNoAlbum($builder, $table, $userContext);
            }

            return;
        }

        switch ($view) {
            case Browse::VIEW_PENDING:
                // Only show pending photos uploaded on any other User. Page/Group will not be included.
                $builder->where($this->alias($table, 'is_approved'), '=', Photo::IS_PENDING)
                    ->where($this->alias($table, 'owner_type'), $userContext->entityType());
                break;
            case Browse::VIEW_MY_PENDING:
                $builder->whereNot($this->alias($table, 'is_approved'), 1)
                    ->where($this->alias($table, 'user_id'), $userContext->entityId())
                    ->where($this->alias($table, 'owner_type'), $userContext->entityType());
                break;

            case Browse::VIEW_MY:
                $builder->where($this->alias($table, 'is_approved'), 1)
                    ->where($this->alias($table, 'user_id'), '=', $userContext->entityId());
                break;

            case Browse::VIEW_LATEST:
                $builder->orderBy($this->alias($table, $model->getKeyName()), 'DESC');
                break;
            case Browse::VIEW_FRIEND:
                $builder->where($this->alias($table, 'is_approved'), 1);
                if (app_active('metafox/friend')) {
                    $builder->join('friends', function (JoinClause $join) use ($userContext, $table) {
                        $join->on('friends.user_id', '=', $table . '.user_id');
                        $join->where('friends.owner_id', '=', $userContext->entityId());
                    });
                }
                break;
            case Browse::VIEW_FEATURE:
                $builder->where('is_featured', 1)
                    ->where('is_approved', 1);
                break;
            case self::VIEW_NO_ALBUM:
                $this->buildQueryViewNoAlbum($builder, $table, $userContext);
                break;
            case Browse::VIEW_SEARCH:
                if (!$userContext->hasPermissionTo('photo.approve')) {
                    $builder->where(function (Builder $builder) use ($userContext, $table) {
                        $builder->where($this->alias($table, 'is_approved'), '=', 1)
                            ->orWhere($this->alias($table, 'user_id'), '=', $userContext->entityId());
                    });
                }

                break;
            default:
                $builder->where($this->alias($table, 'is_approved'), '=', 1);
        }
    }

    protected function buildQueryViewNoAlbum(Builder $builder, string $table, User $userContext): void
    {
        $builder->where([
            [$this->alias($table, 'owner_id'), '=', $userContext->entityId(), 'or'],
            [$this->alias($table, 'user_id'), '=', $userContext->entityId(), 'or'],
        ])->where('album_id', 0);
    }

    protected function handleJoinForMyTag(Builder $builder, string $table): void
    {
        if (!app_active('metafox/friend')) {
            return;
        }

        $taggedFriendBuilder = app('events')->dispatch('friend.get_tag_friend_builder_by_item_type', ['photo'], true);
        if (!$taggedFriendBuilder instanceof Builder) {
            return;
        }

        $taggedFriendBuilder->where('owner_id', $this->getProfileId());

        $builder->leftJoinSub($taggedFriendBuilder, 'tag_friend', function (JoinClause $joinClause) use ($table) {
            $joinClause->on('tag_friend.item_id', '=', $this->alias($table, 'id'));
        });
    }

    protected function handleQueryForMyTag(Builder $builder, string $table): void
    {
        if (!app_active('metafox/friend')) {
            return;
        }

        $builder->orWhere(function (Builder $builder) use ($table) {
            $builder->whereNotNull('tag_friend.id');
        });
    }
}
