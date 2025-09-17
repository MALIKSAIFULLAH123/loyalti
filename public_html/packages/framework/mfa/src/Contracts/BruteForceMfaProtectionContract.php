<?php

namespace MetaFox\Mfa\Contracts;

use Illuminate\Auth\AuthenticationException;

/**
 * Interface BruteForceMfaProtectionContract.
 * @ignore
 */
interface BruteForceMfaProtectionContract
{
    /**
     * Process the security parameters.
     * @param array|null $params
     *
     * @return void
     */
    public function process(?array $params = []): void;

    /**
     * Verify if the current context should pass the security check.
     * Otherwise throw AuthenticationException.
     * @param array|null $params
     *
     * @return void
     * @throws AuthenticationException
     */
    public function verify(?array $params = []): void;

    /**
     * Check if the current context should pass the security check.
     * @param array|null $params
     *
     * @return bool
     */
    public function check(?array $params = []): bool;

    /**
     * @param  int  $userId
     * @return void
     */
    public function clearCache(int $userId): void;
}
