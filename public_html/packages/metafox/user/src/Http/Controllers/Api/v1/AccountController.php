<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use MetaFox\Authorization\Repositories\DeviceRepositoryInterface;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Http\Requests\v1\Account\GetNotificationSettingRequest;
use MetaFox\User\Http\Requests\v1\Account\ItemPrivacySettingsRequest;
use MetaFox\User\Http\Requests\v1\Account\SettingRequest;
use MetaFox\User\Http\Requests\v1\Account\UpdateNotificationSettingRequest;
use MetaFox\User\Http\Requests\v1\User\DeleteRequest;
use MetaFox\User\Http\Requests\v1\User\EditFormRequest;
use MetaFox\User\Http\Requests\v1\User\UpdateEmailRequest;
use MetaFox\User\Http\Requests\v1\User\UpdateInvisibleRequest;
use MetaFox\User\Http\Requests\v1\User\UpdatePhoneNumberRequest;
use MetaFox\User\Http\Requests\v1\User\UpdateRequest;
use MetaFox\User\Http\Requests\v1\UserBlocked\StoreRequest;
use MetaFox\User\Http\Resources\v1\Account\AccountSetting;
use MetaFox\User\Http\Resources\v1\Account\EditPaymentForm;
use MetaFox\User\Http\Resources\v1\Account\EditReviewTagPostForm;
use MetaFox\User\Http\Resources\v1\Account\EditTimezoneForm;
use MetaFox\User\Http\Resources\v1\User\UserDetail;
use MetaFox\User\Http\Resources\v1\User\UserMe;
use MetaFox\User\Http\Resources\v1\UserBlocked\UserBlockedItemCollection;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserVerify;
use MetaFox\User\Notifications\DirectUpdatedPassword;
use MetaFox\User\Repositories\Contracts\AccountSettingRepositoryInterface;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\Eloquent\UserRepository;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Support\Facades\UserVerify as UserVerifyFacade;
use MetaFox\User\Support\UserVerifySupport;

/**
 * Class AccountController.
 *
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountController extends ApiController
{
    public function __construct(
        protected DeviceRepositoryInterface         $deviceRepository,
        protected UserPrivacyRepositoryInterface    $privacyRepository,
        protected UserRepositoryInterface           $userRepository,
        protected AccountSettingRepositoryInterface $accountSettingRepository,
    ) {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @group user/account
     */
    public function findAllBlockedUser(Request $request): JsonResponse
    {
        $user         = $this->getUser();
        $search       = $request->input('q');
        $blockedUsers = UserBlocked::getBlockedUsersCollection($user, $search);

        $data = new UserBlockedItemCollection($blockedUsers);

        return $this->success($data, ['no_result' => ['title' => __p('core::phrase.no_user_found')]]);
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @group user/account
     */
    public function addBlockedUser(StoreRequest $request): JsonResponse
    {
        $params = $request->validated();

        $user = $this->getUser();

        /** @var ContractUser $owner */
        $owner = User::query()->findOrFail($params['user_id']);

        UserBlocked::blockUser($user, $owner);

        return $this->success([
            'redirectTo' => url_utility()->makeApiFullUrl('settings/blocked'),
        ], [], __p('user::phrase.user_successfully_blocked'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @group user/account
     */
    public function deleteBlockedUser(int $id): JsonResponse
    {
        $user = $this->getUser();

        /** @var ContractUser $owner */
        $owner = User::query()->findOrFail($id);

        UserBlocked::unBlockUser($user, $owner);

        return $this->success(null, [], __p('user::phrase.user_successfully_unblocked'));
    }

    /**
     * @param SettingRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     */
    public function getProfileSettings(SettingRequest $request): JsonResponse
    {
        $id   = $request->validated('id');
        $user = UserEntity::getById($id)->detail;

        $data   = $this->privacyRepository->getProfileSettings($id);
        $data[] = $this->privacyRepository->getBirthdaySetting($user);

        return $this->success($data);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @group user/account
     */
    public function updateProfileSettings(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $params = $request->all();

        $this->privacyRepository->updateOtherUserPrivacy(UserEntity::getById($userId)->detail, $params);

        UserPrivacy::validateProfileSettings($userId, $params);

        $this->privacyRepository->updateUserPrivacy($userId, $params);

        return $this->success(null, [], __p('user::phrase.setting_updated_successfully'));
    }

    /**
     * @param SettingRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     */
    public function getProfileMenuSettings(SettingRequest $request): JsonResponse
    {
        $id   = $request->validated('id');
        $data = $this->privacyRepository->getProfileMenuSettings($id);

        return $this->success($data);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @group user/account
     */
    public function updateProfileMenuSettings(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $params = $request->all();
        UserPrivacy::validateProfileMenuSettings($userId, $params);

        $this->privacyRepository->updateUserPrivacy($userId, $params);

        return $this->success(null, [], __p('core::phrase.updated_successfully'));
    }

    /**
     * @param ItemPrivacySettingsRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     */
    public function getItemPrivacySettings(ItemPrivacySettingsRequest $request): JsonResponse
    {
        $id   = $request->validated('id');
        $data = $this->privacyRepository->getItemPrivacySettings($id);

        return $this->success($data);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @group user/account
     */
    public function updateItemPrivacySettings(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $params = $request->all();
        UserPrivacy::validateItemPrivacySettings($userId, $params);

        $this->privacyRepository->updateUserPrivacy($userId, $params);

        return $this->success(null, [], __p('core::phrase.updated_successfully'));
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     */
    public function setting(): JsonResponse
    {
        $context = user();
        $data    = $this->accountSettingRepository->getAccountSettings($context);

        return $this->success($data);
    }

    /**
     * @param UpdateRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     */
    public function updateAccountSetting(UpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $message = $this->userRepository->getVerifyMessage($context->email, $context->phone_number, $params)
            ?: __p('core::phrase.updated_successfully');

        $data = $this->userRepository->update($params, $context->entityId());
        $this->userRepository->updateThemePreference($context, $params);

        // test user account settings.
        if (isset($params['language_id'])) {
            // cleanup cookie userLanguage.
            $prefix = config('session.cookie_prefix');
            setcookie($prefix . 'userLanguage', '', time() + 86400, config('session.cookie_path', '/'));
            $this->navigate('reload');
        }

        $meta = [];

        if (isset($params['new_password'])) {
            $isLogoutOther = Arr::get($params, 'logout_others', 0);

            if ($isLogoutOther) {
                $tokenId = $context->token()?->id;
                $this->deviceRepository->logoutAllByUser($context, $tokenId);
            }

            Notification::send($context, new DirectUpdatedPassword($data));

            $meta = UserFacade::getActionMetaLogoutOtherDevices()->toArray();
        }

        return $this->success(new AccountSetting($data), $meta, $message);
    }

    /**
     * @throws AuthenticationException
     * @group user/account
     */
    public function getTimeZones(): JsonResponse
    {
        $timezones = [];
        if (user()->entityId()) {
            $timezones = UserFacade::getTimeZoneForForm();
        }

        return $this->success($timezones);
    }

    /**
     * @throws AuthenticationException
     * @group user/account
     */
    public function getInvisibleSettings(): JsonResponse
    {
        $data = resolve(UserRepository::class)->getInvisibleSettings(user());

        return $this->success($data, [], '');
    }

    /**
     * @param UpdateInvisibleRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     */
    public function updateInvisibleSettings(UpdateInvisibleRequest $request): JsonResponse
    {
        $params = $request->validated();
        $user   = UserFacade::updateInvisibleMode(user(), $params['invisible']);

        return $this->success([
            'id'           => $user->entityId(),
            'is_invisible' => $user->is_invisible,
        ], [], __p('core::phrase.updated_successfully'));
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     * @deprecated
     */
    public function getNotificationSettings(GetNotificationSettingRequest $request): JsonResponse
    {
        $params   = $request->validated();
        $channel  = $params['channel'];
        $settings = UserFacade::getNotificationSettingsByChannel(user(), $channel);

        return $this->success($settings);
    }

    /**
     * @param UpdateNotificationSettingRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     * @deprecated
     */
    public function updateNotificationSettings(UpdateNotificationSettingRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $channel = $params['channel'];
        $result  = UserFacade::updateNotificationSettingsByChannel(user(), $params);

        if (!$result) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'));
        }

        $settings = UserFacade::getNotificationSettingsByChannel(user(), $channel);

        return $this->success($settings, [], __p('core::phrase.updated_successfully'));
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     */
    public function editTimezoneForm(): JsonResponse
    {
        /** @var User $user */
        $user = user();

        return $this->success(new EditTimezoneForm($user));
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     */
    public function editEmailForm(EditFormRequest $request): JsonResponse
    {
        /** @var User $user */
        $user       = user();
        $params     = $request->validated();
        $resolution = Arr::get($params, 'resolution', 'web');

        $form = UserVerifyFacade::web(UserVerify::ACTION_EMAIL)
            ->editForm($user, $resolution);

        return $this->success($form, $form->getMultiStepFormMeta());
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     */
    public function editPhoneNumberForm(EditFormRequest $request): JsonResponse
    {
        /** @var User $user */
        $user       = user();
        $params     = $request->validated();
        $resolution = Arr::get($params, 'resolution', 'web');

        $form = UserVerifyFacade::web(UserVerify::ACTION_PHONE_NUMBER)
            ->editForm($user, $resolution);

        return $this->success($form, $form->getMultiStepFormMeta());
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     * @group user/account
     */
    public function editPaymentForm(): JsonResponse
    {
        /** @var User $user */
        $user = user();

        return $this->success(new EditPaymentForm($user));
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function editReviewTagPostForm(): JsonResponse
    {
        /** @var User $user */
        $user = user();

        return $this->success(new EditReviewTagPostForm($user));
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        $context    = user();
        $params     = $request->validated();
        $email      = Arr::get($params, 'email');
        $resolution = Arr::get($params, 'resolution', 'web');

        $actionService = UserVerifyFacade::web(UserVerify::ACTION_EMAIL);
        $updateValue   = ['email' => $email];

        $mustVerify = $actionService->mustVerify($context, $updateValue);
        if (!$mustVerify) {
            $data = $this->userRepository->update($updateValue, $context->entityId());

            return $this->success(new UserDetail($data), [], __p('core::phrase.updated_successfully'));
        }

        $actionService->send($context, $email);
        $form = $actionService->verifyForm($context, $email, UserVerifySupport::UPDATE_ACCOUNT_VERIFY, $resolution);

        return $this->success($form, $form->getMultiStepFormMeta());
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function updatePhoneNumber(UpdatePhoneNumberRequest $request): JsonResponse
    {
        $context     = user();
        $params      = $request->validated();
        $phoneNumber = Arr::get($params, 'phone_number');
        $resolution  = Arr::get($params, 'resolution', 'web');

        $actionService = UserVerifyFacade::web(UserVerify::ACTION_PHONE_NUMBER);
        $updateValue   = ['phone_number' => $phoneNumber];

        $mustVerify = $actionService->mustVerify($context, $updateValue);
        if (!$mustVerify) {
            $data = $this->userRepository->update($updateValue, $context->entityId());

            return $this->success(new UserDetail($data), [], __p('core::phrase.updated_successfully'));
        }

        $actionService->send($context, $phoneNumber);
        $form = $actionService->verifyForm($context, $phoneNumber, UserVerifySupport::UPDATE_ACCOUNT_VERIFY, $resolution);

        return $this->success($form, $form->getMultiStepFormMeta());
    }

    public function cancel(DeleteRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        resolve(UserRepositoryInterface::class)->cancelAccount($context, $context->entityId(), $params);

        return $this->success([], [], __p('user::phrase.user_successfully_deleted'));
    }

    public function getVideoSettings(Request $request): JsonResponse
    {
        $context = user();

        $formClass = resolve('core.drivers')->getDriver('form', 'user.account_setting.video', 'web');
        $form      = resolve($formClass, ['resource' => $context]);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }

    public function updateVideoSettings(Request $request, int $id): JsonResponse
    {
        $context = user();
        $params  = $request->all();

        $user = UserEntity::getById($id)->detail;

        resolve(UserRepositoryInterface::class)->updateVideosSettings($context, $user, $params);

        return $this->success(new UserMe($user), [], __p('user::phrase.setting_updated_successfully'));
    }
}
