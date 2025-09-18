<?php

namespace MetaFox\Music\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Music\Models\Song as Model;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SongAdminRepositoryInterface.
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 *
 * @mixin CollectTotalItemStatTrait
 * @mixin BaseRepository
 */
interface SongAdminRepositoryInterface extends HasSponsor, HasSponsorInFeed
{
    /**
     * View songs.
     *
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    public function viewSongs(User $context, array $attributes): Builder;

    /**
     * @param User $context
     * @param int  $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;
}
