<?php

namespace MetaFox\Poll\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use MetaFox\Poll\Models\Poll;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Result.
 * @mixin BaseRepository
 */
interface ResultRepositoryInterface
{
    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Poll
     */
    public function createResult(User $context, array $attributes): Poll;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Poll
     */
    public function updateResult(User $context, int $id, array $attributes): Poll;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Paginator
     */
    public function viewResults(User $context, array $attributes): Paginator;

    /**
     * @param  array $ids
     * @return void
     */
    public function deletePollResultNotificationByIds(array $ids): void;

    /**
     * @param Poll $poll
     */
    public function updateAnswersPercentage(Poll $poll): void;
}
