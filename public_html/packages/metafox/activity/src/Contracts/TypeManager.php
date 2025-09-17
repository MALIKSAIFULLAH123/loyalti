<?php

namespace MetaFox\Activity\Contracts;

use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\TypeManagerInterface;

/**
 * Interface TypeManager.
 */
interface TypeManager extends TypeManagerInterface
{
    /**
     * @param  Feed        $feed
     * @param  int         $profileId
     * @return string|null
     */
    public function getTypePhraseWithContext(Feed $feed, int $profileId = 0): ?string;

    /**
     * @return array
     */
    public function getTypes(): array;

    /**
     * @return array
     */
    public function getAbilities(): array;

    /**
     * @return array
     */
    public function getTypeSettings(): array;

    /**
     * @return void
     */
    public function cleanData(): void;

    /**
     * @return array
     */
    public function getDefaultSettings(): array;

    /**
     * @return array
     */
    public function getDisabledSettings(): array;

    /**
     * @param  string $typeName
     * @param  string $settingName
     * @return bool
     */
    public function isDisabled(string $typeName, string $settingName): bool;

    /**
     * @param  string $type
     * @return array
     */
    public function getDefaultSettingsByType(string $type): array;
}
