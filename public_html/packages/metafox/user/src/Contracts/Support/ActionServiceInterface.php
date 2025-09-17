<?php

namespace MetaFox\User\Contracts\Support;

use MetaFox\Form\AbstractForm;
use MetaFox\User\Models\User;

/**
 * Interface ActionServiceInterface.
 */
interface ActionServiceInterface
{
    /**
     * @param User   $resource
     * @param string $verifiable
     * @param string $verifyPlace
     * @param string $resolution
     *
     * @return null|AbstractForm
     */
    public function verifyForm(User $resource, string $verifiable, string $verifyPlace, string $resolution = 'web'): ?AbstractForm;

    /**
     * @param User   $resource
     * @param string $resolution
     *
     * @return null|AbstractForm
     */
    public function editForm(User $resource, string $resolution = 'web'): ?AbstractForm;

    /**
     * @param User   $user
     * @param string $verifiable
     *
     * @return bool
     */
    public function send(User $user, string $verifiable): bool;

    /**
     * @param User   $user
     * @param string $verifiable
     *
     * @return bool
     */
    public function resend(User $user, string $verifiable): bool;

    /**
     * @param User  $user
     * @param array $extra
     *
     * @return bool
     */
    public function mustVerify(User $user, array $extra = []): bool;

    /**
     * @param ?string $code
     * @param ?string $hash
     *
     * @return User|null
     */
    public function verify(?string $code, ?string $hash = null): ?User;
}
