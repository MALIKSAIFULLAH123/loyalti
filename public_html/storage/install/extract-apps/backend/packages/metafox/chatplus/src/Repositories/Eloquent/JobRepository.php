<?php

namespace MetaFox\ChatPlus\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\ChatPlus\Models\Job;
use MetaFox\ChatPlus\Repositories\JobRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class JobRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class JobRepository extends AbstractRepository implements JobRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Job::class;
    }

    public function addJob(string $name, array $data): void
    {
        $this->create([
            'name' => $name,
            'data' => $data,
        ]);
    }

    /**
     * @param  int        $limit
     * @param  bool       $clear
     * @return Collection
     */
    public function getJobs(int $limit, bool $clear): Collection
    {
        $jobs = $this->getModel()
            ->newQuery()
            ->where('is_sent', 0)
            ->limit($limit)
            ->get();
        foreach ($jobs as $job) {
            if ($clear) {
                $job->delete();
                continue;
            }
            $job->is_sent = 1;
            $job->save();
        }

        return $jobs;
    }
}
