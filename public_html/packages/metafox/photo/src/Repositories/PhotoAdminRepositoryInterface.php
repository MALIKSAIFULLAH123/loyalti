<?php

namespace MetaFox\Photo\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Photo\Models\Photo;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface PhotoAdminRepositoryInterface.
 * @mixin BaseRepository
 * @method Photo getModel()
 * @method Photo find($id, $columns = ['*'])
 * @method Photo newModelInstance()
 *
 * @mixin CollectTotalItemStatTrait
 */
interface PhotoAdminRepositoryInterface extends HasSponsor, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    public function viewPhotos(User $context, array $attributes = []): Builder;

    /**
     * @param User $context
     * @param int  $id
     * @return array
     */
    public function deletePhoto(User $context, int $id): array;

    /**
     * @param User $context
     * @param int  $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;
}
