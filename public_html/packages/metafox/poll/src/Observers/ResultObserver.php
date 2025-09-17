<?php

namespace MetaFox\Poll\Observers;

use Exception;
use MetaFox\Poll\Models\Result;
use MetaFox\Poll\Repositories\ResultRepositoryInterface;

/**
 * Class ResultObserver.
 */
class ResultObserver
{
    public function created(Result $result): void
    {
        //Update total_count of the voted answer
        $answer = $result->answer;
        $poll   = $result->poll;

        $answer->incrementAmount('total_vote');
        $poll->incrementAmount('total_vote');
    }

    /**
     * @throws Exception
     */
    public function deleted(Result $result): void
    {
        //Update total_count of the voted answer
        $poll = $result->poll;

        if (!$poll) {
            return;
        }
        $poll->decrementAmount('total_vote');

        $answer = $result->answer;

        if (!$answer) {
            return;
        }

        $answer->decrementAmount('total_vote');
        resolve(ResultRepositoryInterface::class)->updateAnswersPercentage($poll);
    }
}

// end stub
