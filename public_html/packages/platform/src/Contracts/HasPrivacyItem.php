<?php

namespace MetaFox\Platform\Contracts;

/**
 * Trait HasPrivacyItem.
 *
 * @description if Contract User has item which use privacy based on this Contract User. Use this class.
 * @package     MetaFox\Platform\Contracts
 */
interface HasPrivacyItem
{
    /**
     * Get privacy for item.
     *
     * @return int
     */
    public function getPrivacyItem(): int;
}
