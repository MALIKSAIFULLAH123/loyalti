<?php

namespace MetaFox\User\Contracts;

use Illuminate\Auth\AuthenticationException;

/**
 * Interface UserSecurityContract.
 * @ignore
 */
interface UserSecurityContract
{
    /**
     * Process the security parameters.
     * @param array<mixed>|null $params
     *
     * @return bool
     */
    public function process(?array $params = []): void;

    /**
     * Verify if the current context should pass the security check.
     * Otherwise throw AuthenticationException.
     * @param array<mixed>|null $params
     *
     * @return void
     * @throws AuthenticationException
     */
    public function verify(?array $params = []): void;

    /**
     * Check if the current context should pass the security check.
     * @param array<mixed>|null $params
     *
     * @return bool
     */
    public function check(?array $params = []): bool;

    /**
     * @param  string $address
     * @return void
     */
    public function clearCache(string $address): void;
}
