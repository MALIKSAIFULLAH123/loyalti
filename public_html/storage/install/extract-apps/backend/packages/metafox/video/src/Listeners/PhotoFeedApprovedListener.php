<?php

namespace MetaFox\Video\Listeners;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Repositories\VideoRepositoryInterface;

class PhotoFeedApprovedListener
{
    public function handle(User $context, PhotoGroup $group): void
    {
        $repository = resolve(VideoRepositoryInterface::class);

        $isApproved = $group->is_approved;

        $videos = $repository->getVideosByGroupId($group->entityId());

        if ($videos instanceof Collection) {
            foreach ($videos as $video) {
                switch ($isApproved) {
                    case 1:
                        $video->update(['is_approved' => $isApproved]);
                        break;
                    case 0:
                        $repository->deleteVideo($context, $video->entityId());
                        break;
                }
            }
        }
    }
}
