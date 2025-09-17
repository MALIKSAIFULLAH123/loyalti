<?php

namespace MetaFox\User\Support\Verify\Action;

use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\User\Models\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Contracts\Support\ActionServiceInterface;
use MetaFox\User\Models\UserVerify;
use MetaFox\User\Repositories\UserVerifyRepositoryInterface;
use MetaFox\User\Support\UserVerifySupport;

abstract class AbstractActionService implements ActionServiceInterface
{
    public function __construct(
        protected UserVerifyRepositoryInterface $userVerifyRepository,
        protected DriverRepositoryInterface $driverRepository
    ) {
    }

    abstract protected function homeVerify(User $user): void;

    abstract protected function updateAccountVerify(User $user, UserVerify $verify): void;

    public function sendAbstract(User $user, string $action): bool
    {
        $this->userVerifyRepository->invalidatePendingVerify($user, $action);

        return true;
    }

    public function resendAbstract(User $user, string $action, string $verifiable): bool
    {
        $this->userVerifyRepository->checkResend($user, $action);

        $this->send($user, $verifiable);

        return true;
    }

    protected function verifyAbstract(string $action, ?string $code, ?string $hash = null): ?User
    {
        if ($code) {
            $code = $this->userVerifyRepository->addSuffixCode($code, $action);
            $hash = sha1($code);
        }

        $verify = $this->userVerifyRepository
            ->getModel()
            ->where([
                'is_verified' => 0,
                'hash_code'   => $hash,
                'action'      => $action,
            ])->first();

        $this->userVerifyRepository->commonVerify($verify);

        $user = $verify->user;

        $positionVerifyName = $this->getPositionVerifyName($user, $verify);

        if (method_exists($this, $positionVerifyName)) {
            $this->{$positionVerifyName}($user, $verify);

            $this->maskVerified($user, $verify);
        }

        return $user;
    }

    protected function maskVerified(User $user, UserVerify $verify): void
    {
        $verify->markAsVerified();

        if (!$user->hasVerified()) {
            $user->markAsVerified();
        }
    }

    protected function loadVerifyForm(User $resource, string $verifiable, string $action, string $verifyPlace, string $resolution = 'web'): ?AbstractForm
    {
        $service    = UserVerifySupport::WEB_SERVICE;
        $driverName = "user_verify.{$service}.$action.$verifyPlace";

        return $this->loadForm($resource, $verifiable, $driverName, $resolution);
    }

    protected function getPositionVerifyName(User $user, UserVerify $verify): string
    {
        $verifyField = $verify->action == UserVerify::ACTION_EMAIL ? 'email' : 'phone_number';

        return $verify->verifiable == $user->{$verifyField} ? 'homeVerify' : 'updateAccountVerify';
    }

    protected function loadForm(User $resource, ?string $verifiable, string $driverName, ?string $resolution = 'web'): ?AbstractForm
    {
        $driver = $this->driverRepository
            ->getDriver(Constants::DRIVER_TYPE_FORM, $driverName, $resolution ?? MetaFoxConstant::RESOLUTION_WEB);

        /* @var ?AbstractForm $form */
        $form = resolve($driver, ['resource' => $resource, 'verifiable' => $verifiable]);

        if (!$form instanceof AbstractForm) {
            return null;
        }

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot']);
        }

        return $form;
    }
}
