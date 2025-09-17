<?php

namespace MetaFox\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;

class VerifyStoreInformation extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    public function handle()
    {
        VerifyLatestVersion::dispatch()
            ->delay(now()->addMinutes(random_int(0, 60)));
        VerifyMetaFoxInfo::dispatch()
            ->delay(now()->addMinutes(random_int(0, 60)));
        VerifyProductExpiredDay::dispatch()
            ->delay(now()->addMinutes(random_int(0, 60)));
    }
}
