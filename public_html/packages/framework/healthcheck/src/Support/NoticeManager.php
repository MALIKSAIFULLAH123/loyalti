<?php

namespace MetaFox\HealthCheck\Support;

use Illuminate\Support\Arr;
use MetaFox\HealthCheck\Checks\CheckFilesystemPermission;
use MetaFox\HealthCheck\Checks\CheckLicense;
use MetaFox\HealthCheck\Contracts\NoticeManager as ContractsNoticeManager;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class NoticeManager implements ContractsNoticeManager
{
    public function collectReports(): array
    {
        $checkers = array_merge($this->getDefaultCheckers(), $this->getExtraCheckers());
        $reports  = [];

        foreach ($checkers as $checker) {
            $result = $this->processChecker($checker);
            if (!$result instanceof Result) {
                continue;
            }

            array_push($reports, ...$result->getReports());
        }

        return $reports;
    }

    /**
     * @param array $checker
     *
     * @return Result|null
     */
    private function processChecker(array $checker): ?Result
    {
        $method  = Arr::get($checker, 'method', 'check');
        $handler = Arr::get($checker, 'handler');

        if (!$handler instanceof Checker || !method_exists($handler, $method)) {
            return null;
        }

        if ($method != 'check') {
            $result = new Result();
            $handler->$method($result);

            return $result;
        }

        return $handler->check();
    }

    /**
     * register default checkers.
     * @return array<mixed>
     */
    private function getDefaultCheckers(): array
    {
        return [
            [
                'handler' => resolve(CheckFilesystemPermission::class),
                'method'  => 'checkUnexpectedDirectories',
            ],
            [
                'handler' => resolve(CheckLicense::class),
            ],
        ];
    }

    /**
     * get extra checkers from events.
     * @return array<mixed>
     */
    private function getExtraCheckers(): array
    {
        $extraCheckers = app('events')->dispatch('health-check.checker');

        return array_filter(Arr::map(Arr::flatten($extraCheckers), function ($extraChecker) {
            $checker = $this->transformExtraChecker($extraChecker);
            if (empty($checker)) {
                return null;
            }

            return $checker;
        }));
    }

    /**
     * normalize extra checker responses.
     * @param string|array $extraChecker
     *
     * @return array<mixed>
     */
    private function transformExtraChecker($extraChecker): array
    {
        if (empty($extraChecker)) {
            return [];
        }

        $checker = [
            'handler' => $extraChecker,
        ];

        // allow overridden + custom method
        if (is_array($extraChecker)) {
            if (!Arr::has($extraChecker, 'handler')) {
                return [];
            }

            $checker = $extraChecker;
        }

        if (is_string($checker['handler'])) {
            $checker['handler'] = resolve($checker['handler']);
        }

        return $checker;
    }
}
