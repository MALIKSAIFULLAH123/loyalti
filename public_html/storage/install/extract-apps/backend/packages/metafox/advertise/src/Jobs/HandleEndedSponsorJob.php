<?php

namespace MetaFox\Advertise\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Jobs\AbstractJob;

class HandleEndedSponsorJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        $endedSponsors = Sponsor::query()
            ->with(['item'])
            ->where('status', Support::ADVERTISE_STATUS_APPROVED)
            ->whereNotNull('end_date')
            ->where('end_date', '<=', Carbon::now())
            ->get();

        if (!$endedSponsors->count()) {
            return;
        }

        $endedIds = $endedSponsors->pluck('id')->toArray();

        Sponsor::query()
            ->whereIn('id', $endedIds)
            ->update(['status' => Support::ADVERTISE_STATUS_ENDED]);

        $endedSponsors->each(function ($sponsor) {
            if (!$sponsor->item instanceof Content) {
                return;
            }
            $sponsor->item->disableSponsor();
        });

        $itemTypes = array_unique($endedSponsors->pluck('item_type')->toArray());

        $repository = resolve(SponsorRepositoryInterface::class);

        foreach ($itemTypes as $itemType) {
            $repository->clearCachesByEntityType($itemType);
        }
    }
}
