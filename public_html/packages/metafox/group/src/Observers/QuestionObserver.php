<?php

namespace MetaFox\Group\Observers;

use MetaFox\Group\Models\Question;
use MetaFox\Group\Repositories\QuestionRepositoryInterface;

/**
 * Class QuestionObserver.
 * @ignore
 */
class QuestionObserver
{
    public function created(Question $question): void
    {
        $question->group->incrementAmount('total_question');
    }
    public function deleted(Question $question): void
    {
        $question->group->decrementAmount('total_question');
        resolve(QuestionRepositoryInterface::class)->deleteRelationsOfQuestion($question);
    }
}
