<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Sms\Support\Traits\PhoneNumberTrait;
use MetaFox\User\Http\Requests\v1\UserPassword\LogoutOtherRequest;
use MetaFox\User\Http\Requests\v1\UserPassword\RequestMethodRequest;
use MetaFox\User\Http\Requests\v1\UserPassword\ResetRequest;
use MetaFox\User\Http\Requests\v1\UserPassword\UpdateRequest;
use MetaFox\User\Http\Requests\v1\UserPassword\VerifyRequest;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\PasswordResetTokenRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class UserPasswordController.
 *
 * @codeCoverageIgnore
 * @ignore
 * @group user
 */
class UserPasswordController extends ApiController
{
    use PhoneNumberTrait;

    public function __construct(
        protected UserRepositoryInterface               $repository,
        protected DriverRepositoryInterface             $driverRepository,
        protected PasswordResetTokenRepositoryInterface $tokenRepository,
        protected DeviceRepositoryInterface             $deviceRepository,
    ) {
    }

    /**
     * @param RequestMethodRequest $request
     * @param string $resolution
     *
     * @return JsonResponse
     * @unauthenticated
     */
    public function requestMethod(RequestMethodRequest $request, string $resolution): JsonResponse
    {
        $params = $request->validated();
        $email  = Arr::get($params, 'email');
        $user   = Arr::get($params, 'user');

        if (!$user instanceof User) {
            abort(404, __p('user::validation.cannot_find_this_user'));
        }

        // Sending reset token as a link to update form
        if (Settings::get('user.shorter_reset_password_routine', false)) {
            $token = $this->tokenRepository->createToken($user, ['as_number' => false]);

            $isPhoneNumber = $this->validatePhoneNumber($email);

            $channel = $isPhoneNumber ? 'sms' : 'mail';
            $user->sendPasswordResetToken($token, $channel, 'link');

            $message = $isPhoneNumber
                ? __p('user::phrase.reset_link_sent_to_phone_number')
                : __p('user::phrase.reset_link_sent_to_email');

            return $this->success([], [], $message);
        }

        if ($resolution === 'web') {
            $url        = sprintf('/user/password/request-method?email=%s', urlencode($email));
            $nextAction = [
                'type'    => 'navigate',
                'payload' => ['url' => $url, 'replace' => true],
            ];

            return $this->success([], ['nextAction' => $nextAction]);
        }

        $formDriver = $this->driverRepository
            ->getDriver(Constants::DRIVER_TYPE_FORM, 'user.password.request_method', $resolution);

        /* @var AbstractForm $form */
        $form = resolve($formDriver, ['resource' => $user]);
        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form, $form->getMultiStepFormMeta());
    }

    /**
     * @param VerifyRequest $request
     * @param string $resolution
     *
     * @return JsonResponse
     * @unauthenticated
     */
    public function requestVerify(VerifyRequest $request, string $resolution): JsonResponse
    {
        $params = $request->validated();
        $userId = Arr::get($params, 'user_id');
        $method = Arr::get($params, 'request_method');
        $user   = UserEntity::getById($userId)->detail;
        $token  = $this->tokenRepository->createToken($user, array_merge($params, ['as_numeric' => true]));

        // Sending reset token as a token to enter into app
        if ($user instanceof User) {
            $user->sendPasswordResetToken($token, $method);
        }

        if ($resolution === 'web') {
            $url        = sprintf('/user/password/verify-request?user_id=%s&request_method=%s', $userId, $method);
            $nextAction = [
                'type'    => 'navigate',
                'payload' => ['url' => $url, 'replace' => true],
            ];

            return $this->success([], ['nextAction' => $nextAction]);
        }

        // Return next form
        $formDriver = $this->driverRepository
            ->getDriver(Constants::DRIVER_TYPE_FORM, 'user.password.verify_request', $resolution);

        /* @var AbstractForm $form */
        $form = resolve($formDriver, ['resource' => $user]);
        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form, $form->getMultiStepFormMeta());
    }

    public function edit(UpdateRequest $request, string $resolution): JsonResponse
    {
        $params = $request->validated();
        $userId = Arr::get($params, 'user_id');
        $token  = Arr::get($params, 'token');
        $user   = Arr::get($params, 'user');

        if ($resolution === 'web') {
            $url        = sprintf('/user/password/reset?user_id=%s&token=%s', $userId, $token);
            $nextAction = [
                'type'    => 'navigate',
                'payload' => ['url' => $url, 'replace' => true],
            ];

            return $this->success([], ['nextAction' => $nextAction]);
        }

        // Return next form
        $formDriver = $this->driverRepository
            ->getDriver(Constants::DRIVER_TYPE_FORM, 'user.password.edit', $resolution);

        /* @var AbstractForm $form */
        $form = resolve($formDriver, ['resource' => $user]);
        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form, $form->getMultiStepFormMeta());
    }

    public function reset(ResetRequest $request): JsonResponse
    {
        $params        = $request->validated();
        $userId        = Arr::get($params, 'user_id');
        $token         = Arr::get($params, 'token');
        $newPassword   = Arr::get($params, 'new_password');
        $isLogoutOther = Arr::get($params, 'logout_others', 0);
        $user          = UserEntity::getById($userId)->detail;

        if (!$this->tokenRepository->verifyToken($user, $token)) {
            abort(401, __p('user::phrase.invalid_reset_flow'));
        }

        $this->repository->updateUser($user, $user->entityId(), ['password' => $newPassword]);

        if ($isLogoutOther) {
            $this->deviceRepository->logoutAllByUser($user, null);
            Artisan::call('cache:reset');
        }

        if (MetaFox::getResolution() === 'web') {
            $url        = sprintf('/user/password/logout-all?user_id=%s&token=%s', $userId, $token);
            $nextAction = [
                'type'    => 'navigate',
                'payload' => ['url' => $url, 'replace' => true],
            ];

            return $this->success([], ['nextAction' => $nextAction]);
        }

        $this->tokenRepository->flushTokens($user);

        return $this->success([
            'url'       => '/',
            'user_name' => $user->user_name,
        ], [], __p('passwords.reset'));
    }

    public function logoutAllDevice(LogoutOtherRequest $request): JsonResponse
    {
        $params        = $request->validated();
        $userId        = Arr::get($params, 'user_id');
        $token         = Arr::get($params, 'token');
        $isLogoutOther = Arr::get($params, 'logout_others', 0);
        $user          = UserEntity::getById($userId)->detail;

        if (!$this->tokenRepository->verifyToken($user, $token)) {
            abort(401, __p('user::phrase.invalid_reset_flow'));
        }

        $message = __p('passwords.reset');
        if ($isLogoutOther) {
            $this->deviceRepository->logoutAllByUser($user, null);
            Artisan::call('cache:reset');
            $message = __p('authorization::phrase.logout_other_device_successfully');
        }

        $this->tokenRepository->flushTokens($user);

        return $this->success([
            'url'       => '/',
            'user_name' => $user->user_name,
        ], [], $message);
    }
}
