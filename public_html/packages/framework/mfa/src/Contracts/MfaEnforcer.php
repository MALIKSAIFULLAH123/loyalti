<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Contracts;

use MetaFox\Mfa\Models\EnforceRequest;
use MetaFox\Mfa\Models\UserService;
use MetaFox\User\Models\User;

/**
 * Interface MfaEnforcer.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface MfaEnforcer
{
    /**
     * Check whether we should require the user to enable the MFA and create enforcement request if necessary.
     * @param User $user
     *
     * @return void
     */
    public function process(User $user): void;

    /**
     * Check whether the user has an active enforcement request and take action based on the status of the request.
     * @param User $user
     *
     * @return void
     */
    public function validate(User $user): void;

    /**
     * Handle the process whenever the user service is activated under reminded period.
     * @param UserService $service
     *
     * @return void
     */
    public function onUserServiceActivated(UserService $service): void;

    /**
     * Handle the process whenever the enforcer is disabled,
     * @return void
     */
    public function onEnforcerDisabled(): void;

    /**
     * Handle the process whenever the enforcement request is overdue.
     * @param EnforceRequest $enforceRequest
     *
     * @return void
     */
    public function onRequestOverdue(EnforceRequest $enforceRequest): void;
}
