<?php

namespace MetaFox\Event\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Event\Models\Event;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface EventRepositoryInterface.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 * @method Event getModel()
 * @method Event find($id, $columns = ['*'])()
 */
interface EventAdminRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param User  $context
     * @param array $attributes
     * @return Builder
     */
    public function viewEvents(User $context, array $attributes): Builder;

    /**
     * @param User $context
     * @param int  $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;
}
