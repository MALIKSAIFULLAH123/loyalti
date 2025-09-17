<?php

namespace MetaFox\Quiz\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\Quiz\Models\Quiz;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ResultRepositoryInterface.
 * @mixin BaseRepository
 */
interface ResultRepositoryInterface
{
    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Quiz
     */
    public function createResult(User $context, array $attributes): Quiz;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewResults(User $context, array $attributes): Paginator;

    public function createPlayResult(int $quizId, int $userId): void;

    public function getPlayResult(int $quizId, int $userId);

    public function deletePlayResult(int $quizId): void;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     */
    public function viewResult(User $context, array $attributes);
}
