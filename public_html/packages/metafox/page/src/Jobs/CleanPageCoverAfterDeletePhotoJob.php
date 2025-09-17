<?php

namespace MetaFox\Page\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Jobs\AbstractJob;

class CleanPageCoverAfterDeletePhotoJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    public function __construct(protected Content $photo)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $query = $this->pageRepository()
            ->getModel()
            ->newQuery()
            ->where('cover_id', $this->photo->entityId())
            ->where('cover_type', $this->photo->entityType());

        foreach ($query->cursor() as $page) {
            if (!$page instanceof Page) {
                continue;
            }

            $page->updateQuietly([
                'cover_id'             => 0,
                'cover_file_id'        => 0,
                'cover_photo_position' => 0,
            ]);
        }
    }

    protected function pageRepository(): PageRepositoryInterface
    {
        return resolve(PageRepositoryInterface::class);
    }
}
