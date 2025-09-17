<?php

namespace MetaFox\Forum\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;

/**
 * @mixin CollectTotalItemStatTrait
 */
interface ForumThreadAdminRepositoryInterface extends HasSponsor, HasSponsorInFeed
{
    /**
     * @param User  $context
     * @param array $attributes
     * @return Builder
     */
    public function viewThreads(User $context, array $attributes = []): Builder;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content;
}
