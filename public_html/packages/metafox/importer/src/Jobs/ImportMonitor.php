<?php

namespace MetaFox\Importer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MetaFox\Importer\Repositories\BundleRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * stub: packages/jobs/job-queued.stub.
 * @code
 * \MetaFox\Importer\Jobs\RunImport::dispatchSync()
 * @endcode
 */
class ImportMonitor extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    private BundleRepositoryInterface $bundleRepository;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    public function handle(): void
    {
        // track import is running.
        $this->bundleRepository = resolve(BundleRepositoryInterface::class);
        if (!$this->bundleRepository->isLocking()) {
            return;
        }

        $bundle = $this->bundleRepository->pickStartBundle();

        if (!$bundle) {
            $this->bundleRepository->deleteLockFile();
            // track is importing
            Log::channel('importer')->debug(sprintf('Import successfully.'));
            $this->updateSequences();
            app('events')->dispatch('importer.completed');

            return;
        }

        try {
            Log::channel('importer')
                ->debug(sprintf('%s(%s, %s, %s)', __METHOD__, $bundle->id, $bundle->resource, $bundle->status));

            $bundle->refresh();
            RunBundle::dispatch($bundle->id);
        } catch (\Exception $exception) {
            Log::channel('importer')->error(sprintf('%s:%s', __METHOD__, $exception?->getMessage()));
            $bundle->markAsFailed();
        }
    }

    public function fail(\Exception $exception = null): void
    {
        Log::channel('importer')
            ->error(sprintf('%s:%s', __METHOD__, $exception?->getMessage()));
    }

    public static function updateSequences()
    {
        if (DB::getDriverName() != 'pgsql') {
            return;
        }

        $str = <<<'EOD'
            SELECT 'SELECT SETVAL(' ||
               quote_literal(quote_ident(PGT.schemaname) || '.' || quote_ident(S.relname)) ||
               ', COALESCE(MAX(' ||quote_ident(C.attname)|| '), 1) ) FROM ' ||
               quote_ident(PGT.schemaname)|| '.'||quote_ident(T.relname)|| '; '
            FROM pg_class AS S,
                pg_depend AS D,
                pg_class AS T,
                pg_attribute AS C,
                pg_tables AS PGT
            WHERE S.relkind = 'S'
               AND S.oid = D.objid
               AND D.refobjid = T.oid
               AND D.refobjid = C.attrelid
               AND D.refobjsubid = C.attnum
               AND T.relname = PGT.tablename
               AND PGT.schemaname = 'public'
            ORDER BY S.relname;
            EOD;

        $rows = DB::selectFromWriteConnection($str);

        foreach ($rows as $row) {
            DB::statement($row->{'?column?'});
        }
    }
}
