<?php

namespace MetaFox\Photo\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class DeleteAlbumJob.
 */
class DeleteAlbumJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected User $context, protected Album $album)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        resolve(AlbumRepositoryInterface::class)->deleteAlbumAndPhotos($this->context, $this->album);
    }
}
