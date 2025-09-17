<?php

namespace MetaFox\Newsletter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use MetaFox\Newsletter\Models\Newsletter;
use MetaFox\Newsletter\Repositories\NewsletterAdminRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 * @code
 * @endcode
 */
class NewsletterMonitor extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    public function handle(): void
    {
        $newsletter = resolve(NewsletterAdminRepositoryInterface::class)->pickStartNewsletter();
        if (!$newsletter instanceof Newsletter) {
            return;
        }

        ProcessNewsletterJob::dispatch($newsletter->entityId());
    }
}
