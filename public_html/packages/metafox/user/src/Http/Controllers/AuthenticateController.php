<?php

namespace MetaFox\User\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Notification\Repositories\NotificationChannelRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Http\Requests\UserRegisterRequest;
use MetaFox\User\Http\Resources\v1\User\UserSimple;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Facades\User as Facade;
use MetaFox\User\Support\Facades\UserAuth;
use MetaFox\User\Support\User as UserSupport;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class AuthenticateController.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class AuthenticateController extends ApiController
{
    /**
     * Constructor.
     *
     * @param UserRepositoryInterface                $repository
     * @param NotificationChannelRepositoryInterface $channelRepository
     */
    public function __construct(protected UserRepositoryInterface $repository, protected NotificationChannelRepositoryInterface $channelRepository) {}

    /**
     * @param UserRegisterRequest $request
     *
     * @return mixed
     * @throws ValidatorException
     * @group auth
     * MetaFox
     */
    public function register(UserRegisterRequest $request): mixed
    {
        if (!Settings::get('user.allow_user_registration')) {
            abort(403, __p('user::phrase.user_registration_is_disabled'));
        }

        $params = $request->validated();

        $message = __p('user::phrase.user_registration_was_successful_please_login');
        $setting = Settings::get('user.approve_users');

        $subscribeNotification = (bool) Arr::pull($params, 'subscribe_notification', false);

        if ($setting) {
            $params['approve_status'] = MetaFoxConstant::STATUS_PENDING_APPROVAL;
        }

        $user = $this->repository->createUser($params);

        if (null !== $user) {
            if (Facade::hasPendingSubscription($request, $user)) {
                $message = __p('user::phrase.please_sign_in_to_pay_for_your_subscription');
            }

            if (!$user->isApproved()) {
                $message = __p('user::phrase.your_account_is_now_waiting_for_approval');
            }
        }

        if ($user->mustVerify()) {
            $message = __p('user::phrase.your_registration_is_completed');
        }

        if ($user) {
            app('events')->dispatch('user.registered', [$user]);
            $channels = $this->channelRepository->getAllChannelNames() ?? [];

            $params = [
                UserSupport::SUBSCRIBE_NOTIFICATION_CHANNELS => $subscribeNotification ? $channels : [],
            ];

            $this->repository->updatePreference($user, $params);
        }

        if (!MetaFox::isMobile() && $this->hasAutoLoginAfterRegistration($user)) {
            try {
                $request->merge(['username' => $user->user_name]);

                $response = UserAuth::authorize($request);

                return $this->success(json_decode($response->getContent(), true), [], $message);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->success(new UserSimple($user), [], $message);
    }

    protected function hasAutoLoginAfterRegistration(User $user): bool
    {
        if (!Settings::get('user.enable_auto_login_after_registration')) {
            return false;
        }

        if (!$user?->hasVerified()) {
            return false;
        }

        return $user->isApproved();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @group auth
     * @authenticated
     */
    public function logout(Request $request): JsonResponse
    {
        $context = request()->user();
        if (null === $context) {
            abort(401, __p('user::phrase.user_already_logged_out'));
        }

        app('events')->dispatch('user.logout', [$context, $request]);

        return $this->success([], [], 'Success');
    }

    /**
     * @return JsonResponse
     * @group user
     * @authenticated
     */
    public function profile(): JsonResponse
    {
        return $this->success(request()->user()->load('profile'));
    }
}
