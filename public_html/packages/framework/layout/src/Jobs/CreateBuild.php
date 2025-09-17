<?php

namespace MetaFox\Layout\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use MetaFox\Layout\Models\Build;
use MetaFox\Platform\Jobs\AbstractJob;

class CreateBuild extends AbstractJob
{
    use Dispatchable;

    private string  $reason;
    private ?string $zipFilePath;
    private ?int    $taskId;

    private $response = null;

    /**
     * @param string      $reason
     * @param string|null $zipFilePath
     */
    public function __construct(string $reason, ?string $zipFilePath = null)
    {
        parent::__construct();
        $this->reason      = $reason;
        $this->zipFilePath = $zipFilePath;
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $packageRepository = resolve('core.packages');
        $data              = $packageRepository->getBuildSettings($this->reason);

        $task = new Build([
            'reason'        => $this->reason,
            'job_id'        => '',
            'data'          => json_encode($data),
            'result'        => '',
            'bundle_url'    => '',
            'log_url'       => '',
            'bundle_status' => 'pending',
        ]);

        $task->save();

        CheckBuild::dispatchSync();
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        if (!$this->taskId) {
            return;
        }

        /** @var ?Build $task */
        $task = Build::find($this->taskId);

        if (!$task) {
            return;
        }

        if ($task->expired()) {
            return;
        }

        $task->bundle_status = 'failed';
        $task->result        = 'Failed connecting to build service. ' . $exception->getMessage();
        $task->save();
    }

    public function getResponse()
    {
        return $this->response;
    }
}
