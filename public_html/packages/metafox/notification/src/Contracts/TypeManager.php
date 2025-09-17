<?php

namespace MetaFox\Notification\Contracts;

use MetaFox\Notification\Models\NotificationModule;

interface TypeManager
{
    public function refresh(): void;

    /**
     * Create or update an notification type.
     * Note: this method won't purge cache. Please purge cache manually.
     *
     * @param array<string, mixed> $data
     */
    public function makeType(array $data): void;

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isActive(string $type): bool;

    /**
     * @param string $type
     *
     * @return string|null
     */
    public function getTypePhrase(string $type): ?string;

    /**
     * @param string $type
     * @param string $feature
     *
     * @return bool
     */
    public function hasSetting(string $type, string $feature): bool;

    /**
     * @param string $module
     * @param string $channel
     *
     * @return NotificationModule|null
     */
    public function makeModule(string $module, string $channel): ?NotificationModule;

    /**
     * @param array<mixed> $data
     *
     * @return void
     */
    public function handleDeletedModuleId(array $data): void;

    /**
     * @param array<mixed> $data
     *
     * @return void
     */
    public function handleDeletedTypeByName(array $data): void;

    /**
     * @return array
     */
    public function dataItemTypeMap(): array;

    /**
     * @param string|null $itemType
     * @param int|null    $itemId
     * @return array
     */
    public function transformDataItem(?string $itemType, ?int $itemId): array;

    /**
     * @return array
     */
    public function getSystemTypes(): array;
}
