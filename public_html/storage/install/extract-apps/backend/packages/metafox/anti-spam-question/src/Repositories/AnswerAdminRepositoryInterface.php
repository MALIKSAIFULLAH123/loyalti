<?php

namespace MetaFox\AntiSpamQuestion\Repositories;

use MetaFox\AntiSpamQuestion\Models\Question;

/**
 * Interface Answer
 *
 * stub: /packages/repositories/interface.stub
 */
interface AnswerAdminRepositoryInterface
{
    /**
     * Create answers for a question
     *
     * @param Question $question
     * @param array    $attributes
     */
    public function createAnswer(Question $question, array $attributes): void;

    /**
     * Update answers for a question
     *
     * @param Question $question
     * @param array    $attributes
     */
    public function updateAnswer(Question $question, array $attributes): void;

    /**
     * Remove options from a question
     *
     * @param Question $question
     * @param array    $attributes
     */
    public function removeAnswers(Question $question, array $attributes): void;
}
