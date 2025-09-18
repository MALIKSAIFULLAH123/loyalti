<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\LiveStreaming\Listeners;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Repositories\Eloquent\LiveVideoRepository;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

class StoryGetExtraParamListener
{
    public function __construct(protected LiveVideoRepository $repository) {}

    /**
     * @param User|null $context
     * @param Content   $model
     * @return array|null
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle(?User $context, Content $model): ?array
    {
        $itemId   = $model->item_id;
        $itemType = $model->item_type;

        if (!$itemId || !$itemType) {
            return null;
        }
        if ($itemType !== LiveVideo::ENTITY_TYPE) {
            return null;
        }

        try {
            $liveVideo = $this->repository->viewLiveVideo($context, $itemId);
        } catch (\Exception $exception) {
            return [
                'title'             => __p('livestreaming::phrase.deleted_live_video'),
                'short_description' => null,
                'duration'          => 30,
                'total_viewer'      => 0,
            ];
        }

        $shortDescription = '';
        if ($liveVideo->liveVideoText) {
            $shortDescription = parse_output()->getDescription($liveVideo->liveVideoText->text);
        }
        $duration = (int) $liveVideo->duration;
        if ($liveVideo->is_streaming || !$duration) {
            $duration = 15;
        }

        return [
            'title'             => $liveVideo->toTitle(),
            'short_description' => $shortDescription,
            'duration'          => min($duration, 30),
            'total_viewer'      => $liveVideo->total_viewer,
        ];
    }
}
