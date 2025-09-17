<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

interface UniqueIdInterface
{
    public function getUniqueId(string $itemType): int;
}
