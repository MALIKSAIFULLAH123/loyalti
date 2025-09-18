<?php

namespace MetaFox\Story\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Support\StorySupport;

class LiveStreamCreateStoryListener
{
    public function __construct(protected StoryRepositoryInterface $repository)
    {
    }

    public function handle(Content $model, array $thumbnail, string $playback): ?bool
    {
        // Handle create story
        try {
            $attributes = [
                'extra' => [
                    'image'        => $thumbnail,
                    'video'        => $playback,
                    'is_streaming' => (bool) $model->is_streaming,
                    'is_landscape' => (bool) $model->is_landscape,
                ],
                'item_id'           => $model->entityId(),
                'item_type'         => $model->entityType(),
                'type'              => StorySupport::STORY_TYPE_LIVE_VIDEO,
                'image_file_id'     => 0,
                'thumbnail_file_id' => 0,
                'video_file_id'     => 0,
                'background_id'     => 0,
            ];
            if ($model instanceof AppendPrivacyList) {
                $attributes['privacy'] = $model->privacy ?? 0;
                $attributes['list']    = $model->getPrivacyListAttribute();
            }
            $this->repository->createStory($model->user, $model->owner, $attributes);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
