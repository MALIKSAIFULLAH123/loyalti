<?php

namespace MetaFox\HealthCheck\Checks;

use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;

class CheckServerLoad extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();

        $loadtime = sys_getloadavg();

        if (!$loadtime) {
            $result->error(__p('health-check::phrase.failed_getting_system_load_average'));
        } elseif ($loadtime[0] > 1) {
            $result->warn(__p('health-check::phrase.server_load_is_high') . sprintf(': %.2f, %2.f, %.2f', $loadtime[0], $loadtime[1], $loadtime[2]));
        } else {
            $result->success(__p('health-check::phrase.load_avg') . sprintf(': %.2f, %2.f, %.2f', $loadtime[0], $loadtime[1], $loadtime[2]));
        }

        $this->checkMemoryUsage($result);
        $this->checkDiskspace($result);

        return $result;
    }

    public function checkMemoryUsage(Result $result)
    {
        memory_get_peak_usage();

        memory_get_usage();

        $result->success(__p('health-check::phrase.memory_usage_memory_peak_usage', [
            'memory'      => human_readable_bytes(memory_get_usage()),
            'memory_peak' => human_readable_bytes(memory_get_peak_usage()),
        ]));
    }

    public function checkDiskspace(Result $result)
    {
        $root       = base_path();
        $freeSpace  = disk_free_space($root);
        $totalSpace = disk_total_space($root);
        $percent    = sprintf('%.1f%%', $freeSpace / $totalSpace * 100);

        $result->success(__p('health-check::phrase.disk_free_space_available_of_total', [
            'percent'    => $percent,
            'freeSpace'  => human_readable_bytes($freeSpace),
            'totalSpace' => human_readable_bytes($totalSpace),
        ]));
    }

    public function getName()
    {
        return __p('health-check::phrase.server_status');
    }
}
