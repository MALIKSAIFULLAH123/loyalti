<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasPrivacyType.
 *
 * Privacy type is used when you want to define privacy A for owner resource. But items post on it maybe has different
 * privacy. Example: Closed group has group privacy public, but items on the group still private to member only.
 * Implement this to Contract User example like Group.
 * if ($owner instanceof HasPrivacyType)
 *  $handler = $owner->getPrivacyTypeHandler()
 * @see     PrivacyTypeHandlerInterface
 *  $privacy = $handler->getPrivacy(YOUR_PRIVACY_TYPE_ID)
 *  $privacyForItem = $handler->getPrivacyItem(YOUR_PRIVACY_TYPE_ID)
 *
 * @property int $privacy_type
 * @property int $privacy_item
 * @package MetaFox\Platform\Contracts
 */
interface HasPrivacyType
{
    /**
     * @return mixed
     */
    public function getPrivacyTypeHandler();

    /**
     * Get privacy type.
     *
     * @return int
     */
    public function getPrivacyType(): int;
}
