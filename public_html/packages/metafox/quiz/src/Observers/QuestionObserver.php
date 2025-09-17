<?php

namespace MetaFox\Quiz\Observers;

use MetaFox\Quiz\Models\Question;

/**
 * Class QuestionObserver.
 */
class QuestionObserver
{
    public function deleted(Question $question)
    {
        $question->answers()->delete();
    }
}

// end stub
