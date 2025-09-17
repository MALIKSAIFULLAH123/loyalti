<?php

namespace MetaFox\Importer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MetaFox\Importer\Models\Bundle;
use MetaFox\Importer\Repositories\BundleRepositoryInterface;
use MetaFox\Importer\Supports\JsonImport;
use MetaFox\Importer\Supports\Status;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Platform\Support\JsonImporter;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class RunBundle extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    private BundleRepositoryInterface $bundleRepository;

    private mixed $bundleId;
    private bool  $isRetry;

    public function __construct(mixed $bundleId, $isRetry = false)
    {
        parent::__construct();
        $this->bundleId = $bundleId;
        $this->isRetry  = $isRetry;
    }

    public function uniqueId(): string
    {
        return sprintf('%s:%s', __CLASS__, $this->bundleId);
    }

    public function handle(): void
    {
        Log::channel('importer')
            ->debug(sprintf('%s(%s)', __METHOD__, $this->bundleId));

        $this->bundleRepository = resolve(BundleRepositoryInterface::class);

        /** @var Bundle $bundle */
        $bundle = $this->bundleRepository
            ->find($this->bundleId);

        if (!$bundle) {
            // completed processing.
            return;
        }
        try {
            if ($bundle->isInitial()) {
                $this->processPrepare($bundle);
                if ($this->isRetry) {
                    self::dispatch($bundle->id, true);
                }

                return;
            }

            // continue processing.
            $bundle->markAsProcessing();
            $bundle->saveQuietly();
            $importer = JsonImport::newJsonImporter($bundle->resource);
            $this->processImport($bundle, $importer);
        } catch (\Exception $exception) {
            $bundle->markAsFailed();
            Log::channel('importer')
                ->error(sprintf('Error %s: %s', __METHOD__, $exception->getMessage()));
        }
        $this->shouldContinue();
    }

    public function shouldContinue()
    {
        if ($this->isRetry) {
            return;
        }

        ImportMonitor::dispatch();
    }

    public function processPrepare(Bundle $bundle): void
    {
        try {
            $importer = JsonImport::newJsonImporter($bundle->resource);
            $entries  = $bundle->getJson();
            $bundle->markAsPending();
            $bundle->saveQuietly();
            $importer->setup($entries, $bundle);
            $importer->beforePrepare();
            $importer->processPrepare();
            $importer->afterPrepare();
        } catch (\Exception $exception) {
            Log::channel('importer')
                ->error(sprintf('%s: %s: %s', $bundle->filename, __METHOD__, $exception->getMessage()));
            $bundle->markAsFailed();
        }

        $this->shouldContinue();
    }

    public function processImport(Bundle $bundle, JsonImporter $importer)
    {
        try {
            $data    = mf_get_contents(base_path($bundle->filename));
            $entries = $data ? json_decode($data, true) : [];

            // handle prepare import.
            $importer->setup($entries, $bundle);
            $importer->fillIdFromEntries();
            $importer->beforeImport();
            $importer->processImport();
            $importer->afterImport();
            $bundle->markAsDone();
        } catch (\Exception $exception) {
            $bundle->setStatus(Status::failed);
            Log::channel('importer')
                ->error(sprintf('%s: %s: %s', $bundle->filename, __METHOD__, $exception->getMessage()));
        }
    }

    public function fail(\Exception $exception = null): void
    {
        $bundle = $this->bundleRepository
            ->find($this->bundleId);

        Log::channel('importer')
            ->error('Error handle RunImport: ' . $exception?->getMessage());

        if ($bundle) {
            $bundle->markAsFailed();
            Log::channel('importer')
                ->error(sprintf('Failed resolving resource "%s". Check relation mapping and retry', $bundle->resource));
        }

        $this->shouldContinue();
    }
}
