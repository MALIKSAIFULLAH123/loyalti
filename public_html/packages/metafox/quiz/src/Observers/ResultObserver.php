<?php

namespace MetaFox\Quiz\Observers;

use Exception;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Repositories\ResultRepositoryInterface;

/**
 * Class ResultObserver.
 */
class ResultObserver
{
    /**
     * @param  Result    $result
     * @throws Exception
     */
    public function created(Result $result): void
    {
        // Update the total_play attribute after users submit their result
        $quiz       = $result->quiz;
        $playResult = resolve(ResultRepositoryInterface::class)->getPlayResult($result->quiz_id, $result->user_id);
        if (empty($playResult)) {
            resolve(ResultRepositoryInterface::class)->createPlayResult($result->quiz_id, $result->user_id);
        }
        $quiz->incrementAmount('total_play');
    }

    public function deleted(Result $result): void
    {
        $quiz = $result->quiz;
        $quiz?->decrementAmount('total_play');
        app('events')->dispatch(
            'notification.delete_notification_by_type_and_item',
            ['quiz_result_submitted_notification', $result->entityId(), Result::ENTITY_TYPE]
        );

        $result->items()->delete();
    }
}

// end stub
