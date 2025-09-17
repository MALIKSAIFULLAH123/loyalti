<?php

namespace MetaFox\Poll\Contracts;

use MetaFox\Poll\Models\Poll;

interface PollSupportInterface
{
    /**
     * @return int
     */
    public function getIntegrationViewId(): int;

    /**
     * @param Poll $poll
     * @return array
     */
    public function getStatusTexts(Poll $poll): array;
}
