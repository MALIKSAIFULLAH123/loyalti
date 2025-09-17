<?php

namespace MetaFox\ActivityPoint\Contracts\Support;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\ActivityPoint\Models\ActionType as ActionTypeModel;

interface ActionType
{
    /**
     * @param  Entity               $resource
     * @param  string               $action
     * @return ActionTypeModel|null
     */
    public function getActionType(Entity $resource, string $action): ActionTypeModel|null;

    /**
     * @param  string|null $packageId
     * @return void
     */
    public function setupDefaultActionTypes(?string $packageId = null): void;

    /**
     * @param  string|null $packageId
     * @return void
     */
    public function setupCustomActionTypes(?string $packageId = null): void;

    /**
     * @param  string|null $packageId
     * @return void
     */
    public function setupActionTypesInterpolateFromTransaction(?string $packageId = null): void;

    /**
     * @param  string $packageId
     * @param  mixed  $resource
     * @param  array  $actions
     * @return array
     */
    public function createActionTypesData(string $packageId, mixed $resource, array $actions): array;

    /**
     * @param  string|null $packageId
     * @return void
     */
    public function migrateTransactionExistPointSetting(?string $packageId = null): void;

    /**
     * @param  string|null $packageId
     * @return void
     */
    public function migrateTransactionNotExistPointSetting(?string $packageId = null): void;

    /**
     * @return array
     */
    public function getActionTypeOptions(): array;
}
