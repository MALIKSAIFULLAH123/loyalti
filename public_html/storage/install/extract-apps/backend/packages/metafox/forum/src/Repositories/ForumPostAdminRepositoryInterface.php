<?php

namespace MetaFox\Forum\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ForumPost.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface ForumPostAdminRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     * @return Builder
     */
    public function viewPosts(User $context, array $attributes): Builder;

    /**
     * @param User $context
     * @param int  $id
     * @return bool
     */
    public function deletePost(User $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;
}
