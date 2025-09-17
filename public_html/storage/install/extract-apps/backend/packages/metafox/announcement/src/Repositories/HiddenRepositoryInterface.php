<?php

namespace MetaFox\Announcement\Repositories;

use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Hidden;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface HiddenRepositoryInterface.
 * @mixin BaseRepository
 */
interface HiddenRepositoryInterface
{
    /**
     * @param  User         $context
     * @param  Announcement $resource
     * @return Hidden
     */
    public function createHidden(User $context, Announcement $resource): Hidden;
}
