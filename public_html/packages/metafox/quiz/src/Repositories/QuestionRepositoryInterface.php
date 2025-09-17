<?php

namespace MetaFox\Quiz\Repositories;

use MetaFox\Platform\Contracts\User;

interface QuestionRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     */
    public function viewQuestion(User $context, array $attributes);
}
