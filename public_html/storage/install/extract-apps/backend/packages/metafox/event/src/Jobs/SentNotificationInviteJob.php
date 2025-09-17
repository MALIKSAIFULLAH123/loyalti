<?php

namespace MetaFox\Event\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use MetaFox\Event\Models\Invite;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class SentNotificationInviteJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return sprintf('%s_%s_%s', __CLASS__, $this->eventId, $this->contextId);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $eventId, protected int $contextId, protected array $inviteeIds)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(InviteRepositoryInterface $repository)
    {
        $invites = $repository->getModel()->newModelQuery()
            ->where([
                'user_id'   => $this->contextId,
                'event_id'  => $this->eventId,
                'status_id' => Invite::STATUS_PENDING,
            ])
            ->whereIn('owner_id', $this->inviteeIds)
            ->get();

        foreach ($invites as $invite) {
            if (!$invite instanceof Invite) {
                continue;
            }

            try {
                $notification = $invite->toNotification();
                Notification::send(...$notification);
            } catch (\Exception $e) {
                Log::error('send notification invite error: ' . $e->getMessage());
                Log::error('send notification invite error trace: ' . $e->getTraceAsString());
            }

        }
    }
}
