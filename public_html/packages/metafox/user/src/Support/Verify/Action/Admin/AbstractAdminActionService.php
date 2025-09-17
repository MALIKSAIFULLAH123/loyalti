<?php

namespace MetaFox\User\Support\Verify\Action\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\User\Models\User;
use MetaFox\User\Contracts\Support\ActionServiceInterface;
use MetaFox\User\Repositories\UserVerifyRepositoryInterface;

abstract class AbstractAdminActionService implements ActionServiceInterface
{
    public function __construct(
        protected UserVerifyRepositoryInterface $userVerifyRepository,
    ) {
    }

    abstract public function verifySendingService(): void;

    abstract public function verifyUser(User $user): bool;

    abstract public function send(User $user, string $verifiable): bool;

    public function verifyForm(User $resource, string $verifiable, string $verifyPlace, string $resolution = 'web'): ?AbstractForm
    {
        return null;
    }

    public function editForm(User $resource, string $resolution = 'web'): ?AbstractForm
    {
        return null;
    }

    public function sendAbstract(User $user, string $action): bool
    {
        $this->userVerifyRepository->invalidatePendingVerify($user, $action);

        return true;
    }

    public function resend(User $user, string $verifiable): bool
    {
        if (!$this->verifyUser($user)) {
            return false;
        }

        $this->verifySendingService();
        $this->send($user, $verifiable);

        return true;
    }

    public function mustVerify(User $user, array $extra = []): bool
    {
        return true;
    }

    public function verify(?string $code, ?string $hash = null): ?User
    {
        return null;
    }
}
