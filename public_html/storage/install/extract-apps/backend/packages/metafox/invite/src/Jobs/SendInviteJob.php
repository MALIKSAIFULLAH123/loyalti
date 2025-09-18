<?php

namespace MetaFox\Invite\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
use MetaFox\Invite\Support\Facades\Invite as InviteFacade;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class SendInviteJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $idInvites;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $idInvites)
    {
        parent::__construct();
        $this->idInvites = $idInvites;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        /** @var InviteRepositoryInterface $repository */
        $repository = resolve(InviteRepositoryInterface::class);
        foreach ($this->idInvites as $id) {
            $invite = $repository->getModel()->newQuery()->find($id);
            InviteFacade::send($invite);
        }
    }
}
