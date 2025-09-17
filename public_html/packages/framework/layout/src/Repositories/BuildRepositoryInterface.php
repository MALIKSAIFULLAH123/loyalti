<?php

namespace MetaFox\Layout\Repositories;

use MetaFox\Layout\Models\Build;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Build.
 *
 * @mixin BaseRepository
 * @method Build getModel()
 * @method Build find($id, $columns = ['*'])
 */
interface BuildRepositoryInterface
{
    public function getByBuildId(string $buildId): Build;

    public function findLast(): ?Build;

    public function checkExpiredTasks(): void;
}
