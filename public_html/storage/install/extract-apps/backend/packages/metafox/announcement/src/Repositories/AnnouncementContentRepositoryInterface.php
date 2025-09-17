<?php

namespace MetaFox\Announcement\Repositories;

use MetaFox\Announcement\Models\Announcement;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Announcement\Models\AnnouncementContent as Model;

/**
 * Interface AnnouncementContent.
 *
 * @mixin BaseRepository
 */
interface AnnouncementContentRepositoryInterface
{
    /**
     * @param  Announcement         $announcement
     * @param  array<string, mixed> $attributes
     * @return bool
     */
    public function updateOrCreateContent(Announcement $announcement, array $attributes): ?bool;
}
