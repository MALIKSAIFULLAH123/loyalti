<?php

namespace MetaFox\Poll\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\Poll\Models\Poll;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface PollAdminRepositoryInterface.
 * @mixin BaseRepository
 * @method Poll getModel()
 * @method Poll find($id, $columns = ['*'])
 *
 * @mixin CollectTotalItemStatTrait
 */
interface PollAdminRepositoryInterface extends HasSponsor, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @throws AuthorizationException
     */
    public function viewPolls(User $context, array $attributes): Builder;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content;
}
