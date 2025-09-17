<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Profiling;
use MetaFox\Platform\Facades\RequestLifecycle;

class ProfilingMiddleware
{
    private function enableSqlLog($logger)
    {
        DB::enableQueryLog();

        RequestLifecycle::onTerminated(function () use ($logger) {
            foreach (DB::getQueryLog() as $query) {
                // skip if fast query
                //                if($query['time']< 1) continue;

                $pdo = DB::connection()->getPdo();

                $bindings = array_map(fn ($value) => is_int($value) ? $value : $pdo->quote($value), $query['bindings']);

                $sql = Str::replaceArray('?', $bindings, $query['query']);
                $logger->debug(sprintf('%s ms: %s', $query['time'], $sql));
            }
        });
    }

    private function enableQueryProfiling($logger)
    {
        $collect = [
            'summary' => [
                'time'              => 0,
                'number_of_queries' => 0,
                'php_time'          => 0,
            ],
        ];

        $order = 0;
        app('db')->listen(function (\Illuminate\Database\Events\QueryExecuted $queryExecuted) use (
            &$collect,
            &$order
        ) {
            $collect['summary']['time'] += $queryExecuted->time;
            $collect['summary']['number_of_queries'] += 1;

            $key = md5($queryExecuted->sql);
            if (!isset($collect[$key])) {
                $collect[$key] = [
                    'time'              => 0,
                    'number_of_queries' => 0,
                    'sql'               => $queryExecuted->sql,
                    'order'             => $order += 1,
                    'bindings'          => [],
                ];
            }
            $collect[$key]['time'] += $queryExecuted->time;
            $collect[$key]['number_of_queries'] += 1;
            $collect[$key]['bindings'][] = implode(', ', $queryExecuted->bindings);
        });

        RequestLifecycle::onTerminated(function () use (&$collect, $logger) {
            uasort($collect, function ($a, $b) {
                return $b['time'] - $a['time'];
            });

            if (defined('LARAVEL_START')) {
                $executeTime                         = 1000 * (microtime(true) - LARAVEL_START);
                $collect['summary']['query_percent'] = sprintf(
                    '%.2f%%',
                    $collect['summary']['time'] / $executeTime * 100
                );
                $collect['summary']['php_time'] = $executeTime;
            }

            if (defined('CONTROLLER_START')) {
                $collect['summary']['controller_start'] = 1000 * (CONTROLLER_START - LARAVEL_START);
            }

            if (defined('RESPONSE_START') && defined('LARAVEL_START')) {
                $collect['summary']['response_start'] = 1000 * (RESPONSE_START - LARAVEL_START);
            }

            if (defined('REDUCER_TIME')) {
                $collect['summary']['reducer_time'] = REDUCER_TIME * 1000;
            }

            $logger->debug(json_encode($collect, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        });
    }

    /**
     * @param  Request  $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $enableSqLog = config('app.enable_profiler')
            // && config('app.enable_octane')
            && $request->header('x-profiling');

        if ($enableSqLog) {
            $name = str_replace('/', '_', trim($request->getRequestUri() ?? '', '/'));

            $files = [
                storage_path('logs/' . $name . '.reducer.log'),
                storage_path('logs/' . $name . '.query.log'),
                storage_path('logs/' . $name . '.sql.log'),
            ];

            collect($files)->each(function ($filename) {
                if (file_exists($filename)) {
                    file_put_contents($filename, '');
                }
            });

//            LoadReduce::setLogger(Log::build([
//                'driver' => 'single',
//                'path'   => $files[0],
//            ]));
            //
            //            $this->enableQueryProfiling(Log::build([
            //                'driver' => 'single',
            //                'path'   => $files[1],
            //            ]));

            $this->enableSqlLog(Log::build([
                'driver' => 'single',
                'path'   => $files[2],
            ]));
        }

        return $next($request);
    }

    public function terminate()
    {
        RequestLifecycle::handleTerminated();
        Profiling::dump();
    }
}
