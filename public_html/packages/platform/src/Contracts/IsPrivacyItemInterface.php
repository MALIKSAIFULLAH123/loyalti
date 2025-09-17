<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

interface IsPrivacyItemInterface
{
    /**
     * Result [
     *  [$userId, $itemId, $itemType, $privacyType]
     * ]
     * $userId: to Insert into privacy_data
     * $itemId: $itemType, $privacyType: to find privacy_id.
     *
     * @return array<mixed>
     */
    public function toPrivacyItem(): array;
}
