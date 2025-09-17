<?php

namespace MetaFox\Comment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class DeleteCommentJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int    $id;
    protected string $type;
    protected string $fieldId;
    protected string $fieldType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $id, string $type, string $fieldId, string $fieldType)
    {
        parent::__construct();
        $this->id        = $id;
        $this->type      = $type;
        $this->fieldId   = $fieldId;
        $this->fieldType = $fieldType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $comments = Comment::query()
            ->where($this->fieldId, '=', $this->id)
            ->where($this->fieldType, '=', $this->type)
            ->get();

        foreach ($comments as $comment) {
            try {
                if (!$comment instanceof Comment) {
                    continue;
                }

                $comment->delete();
            } catch (\Throwable $exception) {
            }
        }
    }
}
