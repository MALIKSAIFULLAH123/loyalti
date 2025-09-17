<?php

namespace MetaFox\Core\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class DeleteCategoryJob.
 */
class DeleteImageJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $path;
    public string $serverId;
    /** @var int[] */
    public array $sizes;
    public bool  $isSquare;

    /**
     * DeleteImageJob Constructor.
     *
     * @param string $path
     * @param string $serverId
     * @param int[]  $sizes
     * @param bool   $isSquare
     */
    public function __construct(string $path, string $serverId, array $sizes, bool $isSquare)
    {
        parent::__construct();
        $this->path     = $path;
        $this->serverId = $serverId;
        $this->sizes    = $sizes;
        $this->isSquare = $isSquare;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        // stop this task by Image plugin.
    }
}
