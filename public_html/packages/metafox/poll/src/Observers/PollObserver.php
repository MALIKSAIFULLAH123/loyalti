<?php

namespace MetaFox\Poll\Observers;

use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Support\Facade\Poll as PollFacade;

/**
 * Class PollObserver.
 */
class PollObserver
{
    public function deleted(Poll $poll): void
    {
        if ($poll->view_id != PollFacade::getIntegrationViewId()) {
            app('events')->dispatch('poll.update_thread_integration', [$poll], true);
        }

        $poll->design()->delete();

        $poll->answers()->get()->each(function (Answer $answer) {
            $answer->delete();
        });

        $poll->results()->delete();
    }
}

// end stub
