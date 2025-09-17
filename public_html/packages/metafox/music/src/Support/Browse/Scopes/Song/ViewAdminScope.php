<?php

namespace MetaFox\Music\Support\Browse\Scopes\Song;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewAdminScope extends BaseScope
{
    public const VIEW_DEFAULT  = Browse::VIEW_ALL;
    public const VIEW_NO_ALBUM = 'no_album';
    public const VIEW_APPROVED = 'approved';

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
            [
                'value' => self::VIEW_NO_ALBUM,
                'label' => __p('music::phrase.no_album'),
            ],
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

        switch ($view) {
            case Browse::VIEW_PENDING:
                $builder->where($this->alias($table, 'is_approved'), '=', 0);
                break;
            case self::VIEW_NO_ALBUM:
                $this->buildQueryViewNoAlbum($builder, $table, $userContext);
                break;
            case self::VIEW_APPROVED:
                $builder->where($this->alias($table, 'is_approved'), '=', 1);
                break;
            case Browse::VIEW_FEATURE:
                $builder->where("$table.is_featured", 1)
                    ->where("$table.is_approved", 1);
                break;
            case Browse::VIEW_SPONSOR:
                $builder->where("$table.is_sponsor", '=', 1)
                    ->where("$table.is_approved", 1);
                break;
        }
    }

    protected function buildQueryViewNoAlbum(Builder $builder, string $table, User $userContext): void
    {
        $builder->where([
            [$this->alias($table, 'owner_id'), '=', $userContext->entityId(), 'or'],
            [$this->alias($table, 'user_id'), '=', $userContext->entityId(), 'or'],
        ])->whereNull('album_id');
    }
}
