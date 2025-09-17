<?php

namespace MetaFox\ActivityPoint\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Contracts\User;
use MetaFox\ActivityPoint\Support\Facade\PointConversion;
use MetaFox\ActivityPoint\Models\ConversionAggregate;
use MetaFox\Platform\Jobs\AbstractJob;

class YearExchangedAggregateJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected User $user, protected Carbon $startYear)
    {
        parent::__construct();
    }

    public function handle()
    {
        $record = ConversionAggregate::query()
            ->firstOrCreate([
                'user_id'   => $this->user->entityId(),
                'user_type' => $this->user->entityType(),
                'date'      => $this->startYear,
            ]);

        $total = PointConversion::aggregateConversionRequest($this->startYear, $this->startYear->clone()->endOfYear());

        if ($total == 0) {
            return;
        }

        $record->update(['total' => $total]);
    }
}
