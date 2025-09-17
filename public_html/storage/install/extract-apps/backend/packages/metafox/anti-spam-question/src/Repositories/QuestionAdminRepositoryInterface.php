<?php

namespace MetaFox\AntiSpamQuestion\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use MetaFox\AntiSpamQuestion\Models\Question;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Question
 *
 * @mixin BaseRepository
 * @method Question find($id, $columns = ['*'])
 * stub: /packages/repositories/interface.stub
 */
interface QuestionAdminRepositoryInterface
{
    /**
     * @param array $data
     * @return Question
     */
    public function createQuestion(array $data): Question;

    /**
     * @param Question $model
     * @param array    $data
     * @return Question
     */
    public function updateQuestion(Question $model, array $data): Question;

    /**
     * Orders the questions based on the provided order array.
     *
     * @param User  $context The user performing the action.
     * @param array $orders  An associative array where the key is the order and the value is the question ID.
     *
     * @return void
     */
    public function ordering(User $context, array $orders): void;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     * @return Builder
     */
    public function viewQuestions(User $context, array $attributes): Builder;

    /**
     * Cache all active questions with their answers
     *
     * @return Collection
     */
    public function cacheActiveQuestions(): Collection;
}
