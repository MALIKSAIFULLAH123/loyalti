<?php

namespace MetaFox\Chat\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Chat\Repositories\MessageRepositoryInterface;

class DeleteOrMoveReactionJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected int $reactionId, protected ?int $newReactionId = null)
    {
    }

    public function handle()
    {
        resolve(MessageRepositoryInterface::class)->performActionDeleteOrMoveReaction($this->reactionId, $this->newReactionId);
    }
}
