<?php

namespace MetaFox\Video\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Support\Facade\Video as FacadeVideo;

class MigrateVideoTitle extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $items = Video::query()->with(['group', 'group.activity_feed'])->whereHas('group')->whereHas('group.activity_feed')->cursor();

        foreach ($items as $item) {
            $group = $item->group;
            if (!$group instanceof PhotoGroup) {
                continue;
            }

            if (!$item instanceof Video || $group?->activity_feed?->from_resource !== 'feed') {
                continue;
            }

            $title = '';

            if ($group->items()->count() == 1) {
                $content = $group?->activity_feed?->content ?? '';
                $title   = FacadeVideo::parseVideoTitle(is_string($content) ? $content : '');
            }

            $item->updateQuietly(['title' => parse_input()->clean($title)]);
        }
    }
}
