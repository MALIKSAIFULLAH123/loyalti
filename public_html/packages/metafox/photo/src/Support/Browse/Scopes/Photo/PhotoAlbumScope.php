<?php

namespace MetaFox\Photo\Support\Browse\Scopes\Photo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class PhotoAlbumScope.
 */
class PhotoAlbumScope extends BaseScope
{
    /**
     * @var User
     */
    protected User $user;

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
     * @return PhotoAlbumScope
     */
    public function setUserContext(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $userContext = $this->getUserContext();

        if ($userContext->hasPermissionTo('photo_album.view')) {
            return;
        }

        if ($userContext->hasPermissionTo('photo_album.moderate')) {
            return;
        }

        $builder->where($this->alias($table, 'album_id'), '=', 0);
    }
}
