<?php

namespace MetaFox\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\App\Support\MetaFoxStore;
use MetaFox\Platform\Jobs\AbstractJob;

class VerifyProductExpiredDay extends AbstractJob implements ShouldBeUnique
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
        resolve(MetaFoxStore::class)->verifyExpiredProducts();
    }
}
