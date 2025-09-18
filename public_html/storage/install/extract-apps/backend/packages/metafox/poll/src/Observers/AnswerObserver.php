<?php

namespace MetaFox\Poll\Observers;

use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Result;
use MetaFox\Poll\Repositories\ResultRepositoryInterface;

/**
 * Class PollObserver.
 */
class AnswerObserver
{
    public function deleted(Answer $answer): void
    {
        $this->deleteRelatedResults($answer);
    }

    /**
     * @param  Answer $answer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function deleteRelatedResults(Answer $answer): void
    {
        $repository = resolve(ResultRepositoryInterface::class);

        $results = $repository->getModel()->newModelQuery()
            ->where('answer_id', '=', $answer->entityId())
            ->get();

        $ids = $results->map(function (Result $result) {
            $result->delete();

            return $result->entityId();
        });

        $repository->deletePollResultNotificationByIds(collect($ids)->toArray());
    }
}

// end stub
