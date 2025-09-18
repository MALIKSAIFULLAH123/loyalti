<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\InAppPurchase\Contracts;

use MetaFox\Platform\Contracts\User;

/**
 * Interface InAppPurchaseInterface.
 * payment gateway interface.
 */
interface InAppPurchaseInterface
{
    public function getProductTypes(bool $toForm = true): array;

    public function getProductTypeByValue(string $value): ?array;

    public function handleCallback(string $platform, array $data): bool;

    public function getSettingFormFields(): array;

    public function verifyReceipt(array $data, User $context): bool;
}
