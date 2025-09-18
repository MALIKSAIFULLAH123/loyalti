<?php

namespace MetaFox\Quiz\Listeners;

use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class DisableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != Quiz::ENTITY_TYPE) {
            return;
        }

        resolve(QuizRepositoryInterface::class)->disableFeedSponsor($content);
    }
}
