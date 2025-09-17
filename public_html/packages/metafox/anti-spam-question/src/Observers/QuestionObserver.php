<?php

namespace MetaFox\AntiSpamQuestion\Observers;

use MetaFox\AntiSpamQuestion\Models\Question;

/**
 * stub: /packages/observers/model_observer.stub
 */

/**
 * Class QuestionObserver
 *
 */
class QuestionObserver
{
    public function deleted(Question $question): void
    {
        $question->answers()->delete();
    }
}

// end stub
