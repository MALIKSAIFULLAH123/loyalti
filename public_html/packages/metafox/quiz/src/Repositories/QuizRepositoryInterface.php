<?php

namespace MetaFox\Quiz\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface QuizRepositoryInterface.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface QuizRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Quiz
     * @throws AuthorizationException
     */
    public function createQuiz(User $context, User $owner, array $attributes): Quiz;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Quiz
     */
    public function updateQuiz(User $context, int $id, array $attributes): Quiz;

    /**
     * @param  User                 $context
     * @param  User                 $owner
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewQuizzes(User $context, User $owner, array $attributes): Paginator;

    /**
     * @param  User $context
     * @param  int  $id
     * @return Quiz
     */
    public function viewQuiz(User $context, int $id): Quiz;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteQuiz(User $context, int $id): bool;

    /**
     * @param  User    $context
     * @param  int     $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param  int       $limit
     * @return Paginator
     */
    public function findFeature(int $limit = 4): Paginator;

    /**
     * @param  int       $limit
     * @return Paginator
     */
    public function findSponsor(int $limit = 4): Paginator;

    /**
     * @param  Quiz $quiz
     * @return void
     */
    public function calculateQuizResults(Quiz $quiz): void;
}
