<?php

namespace MetaFox\Core\Listeners\Abstracts;

use Carbon\Carbon;

abstract class AbstractCollectTotalItemStatListener
{
    /**
     * @param Carbon|null $after
     * @param Carbon|null $before
     * @param Carbon|null $group
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?Carbon $after = null, ?Carbon $before = null, ?string $group = null): ?array
    {
        return match ($group) {
            'pending'   => $this->getPendingStats($after, $before),
            'site_stat' => $this->getSiteStats($after, $before),
            default     => $this->getDefaultStats($after, $before),
        };
    }

    /**
     * @param Carbon|null $after
     * @param Carbon|null $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDefaultStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return null;
    }

    /**
     * @param Carbon|null $after
     * @param Carbon|null $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPendingStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return null;
    }

    /**
     * @param Carbon|null $after
     * @param Carbon|null $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getSiteStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return null;
    }
}
