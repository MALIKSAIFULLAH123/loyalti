<?php

namespace MetaFox\User\Listeners;

use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Support\Facades\UserValue;
use MetaFox\User\Support\UserBirthday;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class UserRegisteredListener
{
    public function __construct(protected UserPrivacyRepositoryInterface $userPrivacyRepository)
    {
    }

    /**
     * @param User $user
     *
     * @return void
     */
    public function handle(User $user)
    {
        $this->handleUserVerification($user);
        $this->handleBirthdayPrivacy($user);

        $privacyDefault = Settings::get('user.on_register_privacy_setting');
        $this->userPrivacyRepository->updateUserPrivacy($user->entityId(), ['profile:view_profile' => $privacyDefault]);
    }

    private function handleUserVerification(User $user): void
    {
        if ($user->hasVerified()) {
            return;
        }

        if (!$user->mustVerify()) {
            if ($user->shouldVerifyPhoneNumber()) {
                $user->markPhoneNumberAsVerified();
            }

            if ($user->shouldVerifyEmailAddress()) {
                $user->markEmailAsVerified();
            }

            $user->markAsVerified();

            return;
        }

        if ($user->mustVerifyEmailAddress()) {
            app('user.verification')->sendVerificationEmail($user, $user->email);
        }

        if ($user->mustVerifyPhoneNumber()) {
            app('user.verification')->sendVerificationPhoneNumber($user, $user->phone_number);
        }
    }

    private function handleBirthdayPrivacy(User $user): void
    {
        $defaultBirthdayPrivacy = Settings::get('user.default_birthday_privacy', UserBirthday::DATE_OF_BIRTH_SHOW_ALL);

        UserValue::updateUserValueSetting($user, ['user_profile_date_of_birth_format' => $defaultBirthdayPrivacy]);
    }
}
