<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Like\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * Interface MobileAppAdapter.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface MobileAppAdapter
{
    /**
     * send contact information to the configured recipients.
     *
     * @param $index
     * @param $version
     * @return int
     */
    public function toCompatibleData($index, $version): int;

    /**
     * @param $id
     * @param $version
     * @return int
     */
    public function transformLegacyData($id, $version): int;

    /**
     * @return Collection
     */
    public function getReactionsForConfig(): Collection;
}
