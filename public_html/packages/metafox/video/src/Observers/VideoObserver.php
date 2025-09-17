<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Observers;

use MetaFox\Photo\Models\Album;
use MetaFox\Video\Models\Video;

class VideoObserver
{
    public function deleted(Video $video): void
    {
        $video->videoText()->delete();

        $video->categories()->sync([]);

        // Delete all storage file
        app('storage')->deleteAll($video->image_file_id);

        if ($video->thumbnail_file_id) {
            app('storage')->deleteAll($video->thumbnail_file_id);
        }

        app('events')->dispatch('notification.delete_mass_notification_by_item', [$video], true);
    }

    public function updated(Video $video): void
    {
        if ($video->isDirty(['is_approved'])) {
            $this->increasePhotoGroupTotal($video);
            $this->increasePhotoAlbumTotal($video);
        }
    }

    private function increasePhotoGroupTotal(Video $video): void
    {
        if (!$video->group_id) {
            return;
        }

        if (!$video->isApproved()) {
            return;
        }

        $group = $video->group;

        if (null === $group) {
            return;
        }

        app('events')->dispatch('photo.group.update_media_statistic', [$video], true);

        app('events')->dispatch('photo.group.increase_total_item', [$group, $video->entityType()], true);
    }

    private function increasePhotoAlbumTotal(Video $video): void
    {
        if (!$video->isApproved()) {
            return;
        }

        app('events')->dispatch('photo.album.increase_total_item', [$video], true);
    }
}
