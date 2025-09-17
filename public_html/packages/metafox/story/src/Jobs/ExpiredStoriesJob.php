<?php

namespace MetaFox\Story\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StorySet;
use MetaFox\Story\Notifications\NewStoryToFollowerNotification;
use MetaFox\Story\Notifications\RenewStoryNotification;
use MetaFox\Story\Repositories\StoryRepositoryInterface;


/**
 * stub: packages/jobs/job-queued.stub
 */
class ExpiredStoriesJob extends AbstractJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $nowTimestamp = Carbon::now()->subMinute()->timestamp;

        $storySets = StorySet::query()->where('auto_archive', MetaFoxConstant::IS_ACTIVE)->get();

        if ($storySets->isEmpty()) {
            return;
        }

        $notifyType = (new NewStoryToFollowerNotification())->getType();

        foreach ($storySets as $storySet) {
            /**@var StorySet $storySet */
            $query = $this->storyRepository()->getModel()->newQuery()
                ->where('expired_at', '<', $nowTimestamp)
                ->where('set_id', $storySet->entityId())
                ->where('is_archive', MetaFoxConstant::IS_INACTIVE);
            $this->handleSendNotification($storySet, $nowTimestamp);

            $storyIds = $query->pluck('id')->toArray();

            $query->update(['is_archive' => MetaFoxConstant::IS_ACTIVE]);

            $this->handleDeleteNotify($notifyType, $storyIds);
        }
    }

    protected function handleSendNotification(StorySet $storySet, $nowTimestamp): void
    {
        $story = $this->storyRepository()->getModel()->newQuery()
            ->where('expired_at', '<', $nowTimestamp)
            ->where('set_id', $storySet->entityId())
            ->where('is_archive', MetaFoxConstant::IS_INACTIVE)
            ->orderByDesc('created_at')
            ->first();

        if ($story && $story->expired_at >= $storySet->expired_at) {
            $notification = new RenewStoryNotification($story);
            $params       = [$story->user, $notification];

            Notification::send(...$params);
        }
    }

    protected function handleDeleteNotify(string $notifyType, array $storyIds): void
    {
        app('events')->dispatch('notification.delete_notification_by_items', [$notifyType, $storyIds, Story::ENTITY_TYPE], true);
    }

    protected function storyRepository(): StoryRepositoryInterface
    {
        return resolve(StoryRepositoryInterface::class);
    }
}
