<?php

namespace MetaFox\Newsletter\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Newsletter\Models\Newsletter;
use MetaFox\Newsletter\Repositories\NewsletterAdminRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class ProcessNewsletterJob.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class ProcessNewsletterJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $newsletterId)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $newsletter = $this->newsletterRepository()->getNewsletter($this->newsletterId);

        if (!$newsletter instanceof Newsletter) {
            return;
        }

        $this->prepareNewsletterForSending($newsletter);

        if (!$this->newsletterRepository()->shouldSend($newsletter)) {
            return;
        }

        $this->processSendNewsletter($newsletter);
    }

    private function processSendNewsletter(Newsletter $newsletter): void
    {
        $userIds = $this->newsletterRepository()->getUserIdsForNewsletter($newsletter);

        if (empty($userIds)) {
            $newsletter->update([
                'total_users' => $newsletter->total_sent,
                'status'      => Newsletter::COMPLETED_STATUS,
            ]);

            return;
        }

        $collection = $this->newsletterRepository()->sliceUserIds($newsletter, $userIds);

        $newsletter->refresh();

        if (!$this->newsletterRepository()->shouldSend($newsletter)) {
            return;
        }

        $this->newsletterRepository()->handleSendNewsletter($newsletter, $collection);

        $newsletter->refresh();

        $newsletter->update([
            'status' => $newsletter->total_sent < $newsletter->total_users
                ? Newsletter::PENDING_STATUS
                : Newsletter::COMPLETED_STATUS,
        ]);
    }

    private function prepareNewsletterForSending(Newsletter $newsletter): void
    {
        $newsletter->updateQuietly(['status' => Newsletter::SENDING_STATUS]);
        $this->newsletterRepository()->updateTotalUser($newsletter);
    }

    private function newsletterRepository(): NewsletterAdminRepositoryInterface
    {
        return resolve(NewsletterAdminRepositoryInterface::class);
    }
}
