<?php

namespace MetaFox\User\Contracts\Support;

/**
 * Interface PrivacyForSettingInterface.
 */
interface PrivacyForSettingInterface
{
    /**
     * @return array<int, string>
     */
    public function getPrivacyOptionsPhrase(): array;

    /**
     * @return int
     */
    public function getDefaultPrivacy(): int;
}
