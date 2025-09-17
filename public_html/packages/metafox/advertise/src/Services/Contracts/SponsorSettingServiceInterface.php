<?php

namespace MetaFox\Advertise\Services\Contracts;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

interface SponsorSettingServiceInterface
{
    /**
     * @param  int    $roleId
     * @param  string $guard
     * @return array
     */
    public function getPackageSettings(int $roleId, string $guard = 'api'): array;

    /**
     * @param  User  $user
     * @param  int   $roleId
     * @param  array $params
     * @return bool
     */
    public function updateSettings(User $user, int $roleId, array $params): bool;

    /**
     * @param  string   $var
     * @param  int|null $roleId
     * @return array
     */
    public function getPriceValue(string $var, ?int $roleId = null): array;

    /**
     * @param  User       $user
     * @param  Content    $resource
     * @return float|null
     */
    public function getPriceForPayment(User $user, Content $resource, ?string $currencyId = null): ?float;
}
