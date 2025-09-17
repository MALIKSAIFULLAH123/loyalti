<?php

namespace MetaFox\Photo\Support\Browse\Scopes\Album;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\Album;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\User\Models\User as UserModel;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT             = Browse::VIEW_ALL;

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            Browse::VIEW_ALL,
            Browse::VIEW_MY,
            Browse::VIEW_FEATURE,
            Browse::VIEW_SPONSOR,
            Browse::VIEW_SEARCH,
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
     * @var bool
     */
    protected bool $isViewOwner = false;

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
        if ($this->isViewOwner()) {
            return;
        }

        $table       = $model->getTable();
        $view        = $this->getView();
        $userContext = $this->getUserContext();

        switch ($view) {
            case Browse::VIEW_MY:
                $builder->where($this->alias($table, 'user_id'), '=', $userContext->entityId())
                    ->where(function (Builder $query) use ($table, $userContext) {
                        $query->where($this->alias($table, 'owner_type'), '<>', UserModel::ENTITY_TYPE)
                            ->orWhere(function (Builder $query) use ($table, $userContext) {
                                $query->where($this->alias($table, 'owner_type'), '=', $userContext->entityType())
                                    ->where($this->alias($table, 'owner_id'), '=', $userContext->entityId());
                            });
                    })
                    ->where(function (Builder $query) use ($table) {
                        $query->where($this->alias($table, 'album_type'), '<>', Album::NORMAL_ALBUM)
                            ->orWhere($this->alias($table, 'album_type'), '=', Album::NORMAL_ALBUM);
                    });
                break;
            default:
                $builder->where(function (Builder $query) use ($table, $userContext) {
                    $query->where($this->alias($table, 'total_item'), '>', 0)
                        ->orWhere($this->alias($table, 'user_id'), '=', $userContext->entityId());
                });

                switch ($view) {
                    case Browse::VIEW_SEARCH:
                        $builder->where(function (Builder $builder) use ($userContext, $table) {
                            $builder->where($this->alias($table, 'is_approved'), '=', Album::IS_APPROVED)
                                ->orWhere($this->alias($table, 'user_id'), '=', $userContext->entityId());
                        });

                        break;
                    default:
                        $builder->where($this->alias($table, 'is_approved'), '=', Album::IS_APPROVED);
                        break;
                }
        }
    }
}
