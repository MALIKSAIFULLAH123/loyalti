<?php

namespace MetaFox\Music\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface PlaylistRepositoryInterface.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 */
interface PlaylistAdminRepositoryInterface
{
    /**
     * View playlist.
     *
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    public function viewPlaylists(User $context, array $attributes): Builder;
}
