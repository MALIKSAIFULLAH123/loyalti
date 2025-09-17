<?php

namespace MetaFox\Marketplace\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Marketplace\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

class InviteUserJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected int $contextId, protected int $listingId, protected array $userIds)
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle()
    {
        resolve(InviteRepositoryInterface::class)->inviteFriendsToListing($this->contextId, $this->listingId, $this->userIds);
    }
}
