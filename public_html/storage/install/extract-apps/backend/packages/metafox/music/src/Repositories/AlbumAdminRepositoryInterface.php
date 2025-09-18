<?php

namespace MetaFox\Music\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface AlbumAdminRepositoryInterface.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 */
interface AlbumAdminRepositoryInterface extends HasSponsor, HasSponsorInFeed
{
    /**
     * View albums.
     *
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    public function viewAlbums(User $context, array $attributes): Builder;
}
