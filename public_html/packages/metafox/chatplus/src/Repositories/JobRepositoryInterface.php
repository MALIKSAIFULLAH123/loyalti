<?php

namespace MetaFox\ChatPlus\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Job.
 * @mixin BaseRepository
 */
interface JobRepositoryInterface
{
    /**
     * @param string       $name
     * @param array<mixed> $data
     */
    public function addJob(string $name, array $data): void;

    /**
     * @param  int        $limit
     * @param  bool       $clear
     * @return Collection
     */
    public function getJobs(int $limit, bool $clear): Collection;
}
