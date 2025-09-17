<?php

namespace MetaFox\Photo\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Album.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface AlbumAdminRepositoryInterface extends HasSponsor
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    public function viewAlbums(User $context, array $attributes = []): Builder;

    /**
     * @param User $context
     * @param int  $id
     * @return bool
     */
    public function deleteAlbum(User $context, int $id): bool;
}
