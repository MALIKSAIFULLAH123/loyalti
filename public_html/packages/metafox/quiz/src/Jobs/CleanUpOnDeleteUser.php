<?php

namespace MetaFox\Quiz\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Quiz\Models\PlayedResult;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\Quiz\Repositories\ResultRepositoryInterface;

class CleanUpOnDeleteUser extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int    $userId;
    private string $userType;

    public function uniqueId(): string
    {
        return sprintf('%s_%s_%s', __CLASS__, $this->userId, $this->userType);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $userId = 0, string $userType = 'user')
    {
        parent::__construct();
        $this->userId   = $userId;
        $this->userType = $userType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->deleteQuizzes($this->userId, $this->userType);

            $this->deleteResults($this->userId, $this->userType);

            $this->deletePlayedResult($this->userId);
        } catch (\Exception $error) {
            Log::error($error->getMessage());
            Log::error($error->getTraceAsString());

            // Fail job after done logging error
            $this->fail($error);
        }
    }

    protected function deleteQuizzes(int $userId, string $userType): void
    {
        $repository = resolve(QuizRepositoryInterface::class);

        $repository->lazyDeleteWhere([
            'user_id'   => $userId,
            'user_type' => $userType,
        ]);

        $repository->lazyDeleteWhere([
            'owner_id'   => $userId,
            'owner_type' => $userType,
        ]);
    }

    protected function deleteResults(int $userId, string $userType): void
    {
        $repository = resolve(ResultRepositoryInterface::class);

        $repository->lazyDeleteWhere([
            'user_id'   => $userId,
            'user_type' => $userType,
        ]);
    }

    /**
     * No need to dispatch model event at this moment.
     *
     * @param int $userId
     * @return void
     */
    protected function deletePlayedResult(int $userId): void
    {
        PlayedResult::query()->where('user_id', $userId)->delete();
    }
}
