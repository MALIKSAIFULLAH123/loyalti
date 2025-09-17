<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasGlobalSearch.
 */
interface HasGlobalSearch
{
    /**
     * @return array|null
     */
    public function toSearchable(): ?array;
}
