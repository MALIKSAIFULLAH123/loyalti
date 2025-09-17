<?php

namespace MetaFox\Event\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MetaFox\Event\Models\Event;
use MetaFox\Platform\Jobs\AbstractJob;

class CleanUpCorruptedUserOwnerEventJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Event::query()
            ->doesntHave('owner')
            ->orWhereHas('owner', function (Builder $query) {
                $query->whereNotNull('deleted_at');
            })
            ->get()
            ->collect()
            ->each(function (Event $event) {
                try {
                    $event->delete();
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }
            });
    }
}
