<?php

namespace MetaFox\Music\Support\Browse\Scopes\Song;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class MusicAlbumScope.
 */
class MusicAlbumScope extends BaseScope
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
     * @return MusicAlbumScope
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

        if ($userContext->hasPermissionTo('music_album.view')) {
            return;
        }

        if ($userContext->hasPermissionTo('music_album.moderate')) {
            return;
        }

        $builder->whereNull($this->alias($table, 'album_id'));
    }
}
