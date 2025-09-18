<?php

namespace MetaFox\Quiz\Observers;

use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Repositories\ResultRepositoryInterface;

/**
 * Class QuizObserver.
 */
class QuizObserver
{
    /**
     * @param Quiz $quiz
     */
    public function deleted(Quiz $quiz): void
    {
        $quiz->quizText()->delete();
        $quiz->tagData()->sync([]);
        $quiz->questions()->each(function (Question $question) {
            $question->answers()->delete();
        });
        $quiz->questions()->delete();
        $resultIds = $quiz->results()->get()->collect()->pluck('id')->toArray();
        app('events')->dispatch(
            'notification.delete_notification_by_items',
            ['quiz_result_submitted_notification', $resultIds, Result::ENTITY_TYPE]
        );
        app('events')->dispatch(
            'notification.delete_notification_by_type_and_item',
            ['quiz_resubmit_notification', $quiz->entityId(), $quiz->entityType()]
        );
        $quiz->results()->delete();
        resolve(ResultRepositoryInterface::class)->deletePlayResult($quiz->entityId());
    }
}

// end stub
