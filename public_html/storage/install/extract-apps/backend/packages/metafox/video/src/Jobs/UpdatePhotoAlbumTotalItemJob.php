<?php

namespace MetaFox\Video\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Photo\Models\Album;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class UpdatePhotoAlbumTotalItemJob.
 */
class UpdatePhotoAlbumTotalItemJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        Album::withoutEvents(function () {
            foreach (Album::query()->cursor() as $album) {
                if (!$album instanceof Album) {
                    continue;
                }

                $album->update([
                    'total_item' => $album->approvedItems()->count(),
                ]);
            }
        });
    }
}
