<?php

namespace MetaFox\Platform\Traits\Helpers;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;

/**
 * Trait IsLikedTrait.
 */
trait IsLikedTrait
{
    /**
     * @param User        $context
     * @param Entity|null $content
     *
     * @return bool
     */
    public function isLike(User $context, ?Entity $content = null): bool
    {
        if ($content === null) {
            return false;
        }

        return
            $content instanceof HasTotalLike
            && app_active('metafox/like')
            && app('events')->dispatch('like.is_liked', [$context, $content], true);
    }
}
