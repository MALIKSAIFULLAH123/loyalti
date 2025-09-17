<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Models\UserRelationHistory;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class DeletedRelationJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $relationId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $relationId)
    {
        parent::__construct();
        $this->relationId = $relationId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        /*
         * Update Profile user->relation = null and user->relation_with = null
         * Delete UserRelationHistories => Delete Feed type user_relation_history
         */

        $this->handleProfileUser($this->relationId);
        $this->handleRelationHistories($this->relationId);
    }

    private function handleProfileUser(int $relationId): void
    {
        $profile = new UserProfile();
        $profile->newQuery()
            ->where('relation', $relationId)
            ->update([
                'relation'      => 0,
                'relation_with' => 0,
            ]);
    }

    private function handleRelationHistories(int $relationId): void
    {
        $histories = new UserRelationHistory();
        $item      = $histories->newQuery()
            ->where('relation_id', $relationId)->first();

        if ($item instanceof UserRelationHistory) {
            $item->activity_feed->delete();
            $item->delete();
        }
    }
}
