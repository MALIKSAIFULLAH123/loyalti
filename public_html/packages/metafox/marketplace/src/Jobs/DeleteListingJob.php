<?php

namespace MetaFox\Marketplace\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

class DeleteListingJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected int $listingId)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle()
    {
        resolve(ListingRepositoryInterface::class)->forceDeleteListing($this->listingId);
    }
}
