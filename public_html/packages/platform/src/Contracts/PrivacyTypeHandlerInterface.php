<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface PrivacyTypeHandlerInterface.
 */
interface PrivacyTypeHandlerInterface
{
    /**
     * Define privacy based on privacy_type.
     *
     * @param  int $typeId
     * @return int
     */
    public function getPrivacy(int $typeId): int;

    /**
     * Define privacy for item based on privacy_type.
     *
     * @param  int $typeId
     * @return int
     */
    public function getPrivacyItem(int $typeId): int;
}
