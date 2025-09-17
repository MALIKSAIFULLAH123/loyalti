<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Repositories\ExportProcessRepositoryInterface;

class ExportUserProcessingJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected ?int $processId)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $model = $this->exportProcessRepository()->find($this->processId);
        $this->exportProcessRepository()->exportCSV($model);
    }

    protected function exportProcessRepository(): ExportProcessRepositoryInterface
    {
        return resolve(ExportProcessRepositoryInterface::class);
    }
}
