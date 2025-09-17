<?php

namespace MetaFox\Blog\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Blog\Models\Blog;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Interface BlogRepositoryInterface.
 * @method Blog find($id, $columns = ['*'])
 * @method Blog getModel()
 *
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface BlogAdminRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewBlogs(User $context, array $attributes): Paginator;

    /**
     * @param User $context
     * @param int  $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;
}
