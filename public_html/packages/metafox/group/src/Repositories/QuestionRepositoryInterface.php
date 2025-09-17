<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Models\Request;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface QuestionRepositoryInterface.
 * @mixin BaseRepository
 */
interface QuestionRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function getQuestions(User $context, array $attributes): Paginator;

    /**
     * @param int $groupId
     *
     * @return Collection
     * @throws AuthorizationException
     */
    public function getQuestionsForForm(int $groupId): ?Collection;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Question
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createQuestion(User $context, array $attributes): Question;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Question
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function updateQuestion(User $context, int $id, array $attributes): Question;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteQuestion(User $context, int $id): bool;

    /**
     * @param Question $question
     *
     * @return Question
     */
    public function deleteRelationsOfQuestion(Question $question): Question;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @param  int                  $requestId
     * @return bool
     */
    public function createAnswer(User $context, array $attributes, int $requestId): bool;

    public function getAnswersByRequestId(User $context, Request $request): Collection;
}
