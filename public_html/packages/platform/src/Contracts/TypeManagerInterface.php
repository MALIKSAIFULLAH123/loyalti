<?php

namespace MetaFox\Platform\Contracts;

use Throwable;

interface TypeManagerInterface
{
    /**
     * @param string $type
     *
     * @return bool
     */
    public function isActive(string $type): bool;

    /**
     * @param string $type
     * @param string $feature
     *
     * @return bool
     */
    public function hasFeature(string $type, string $feature): bool;

    /**
     * @param string $type
     * @param string $feature
     *
     * @return bool
     */
    public function hasSetting(string $type, string $feature): bool;

    /**
     * Refresh cache.
     *
     * @throws Throwable
     */
    public function refresh(): void;

    /**
     * Create or update an item type.
     * Note: this method won't purge cache. Please purge cache manually.
     *
     * @param array<string, mixed> $data
     *
     * @return mixed|false
     */
    public function makeType(array $data);

    /**
     * @param string $type
     *
     * @return string|null
     */
    public function getTypePhrase(string $type): ?string;
}
