<?php

namespace MetaFox\Like\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Like\Models\Reaction;
use MetaFox\Like\Repositories\ReactionAdminRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class DeleteReactionJob.
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteReactionJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Reaction $reaction;

    protected int $newReactionId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Reaction $reaction, int $newReactionId)
    {
        parent::__construct();
        $this->reaction      = $reaction;
        $this->newReactionId = $newReactionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        /**@var  ReactionAdminRepositoryInterface $repository */
        $repository = resolve(ReactionAdminRepositoryInterface::class);
        $repository->deleteOrMoveToNewReaction($this->reaction, $this->newReactionId);
    }
}
