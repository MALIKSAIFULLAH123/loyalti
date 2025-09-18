<?php

namespace MetaFox\Sticker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Sticker\Models\StickerRecent;
use MetaFox\Sticker\Models\StickerSet;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;


/**
 * stub: packages/jobs/job-queued.stub
 */
class DeleteStickerRecentJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__ . $this->stickerSetId;
    }

    /**
     * Create a new job instance.
     *
     * @param int $stickerSetId
     * @return void
     */
    public function __construct(protected int $stickerSetId)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle(): void
    {
        $stickerSet = $this->setRepository()
            ->getModel()
            ->newQuery()
            ->find($this->stickerSetId);

        if (!$stickerSet instanceof StickerSet) {
            return;
        }

        $stickerIds = $stickerSet->stickers->pluck('id')->toArray();

        StickerRecent::query()->whereIn('sticker_id', $stickerIds)->delete();
    }

    protected function setRepository(): StickerSetRepositoryInterface
    {
        return resolve(StickerSetRepositoryInterface::class);
    }
}
