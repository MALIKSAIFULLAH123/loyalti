<?php

namespace MetaFox\User\Support;

use Exception;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Contracts\UserBirthday as UserBirthdayContracts;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Support\Facades\UserValue;

/**
 * Interface UserBirthday.
 */
class UserBirthday implements UserBirthdayContracts
{
    public const DATE_OF_BIRTH_DONT_SHOW      = 1;
    public const DATE_OF_BIRTH_SHOW_DAY_MONTH = 2;
    public const DATE_OF_BIRTH_SHOW_AGE       = 3;
    public const DATE_OF_BIRTH_SHOW_ALL       = 4;
    public const DATE_OF_BIRTH_ENUM_TODAY     = 0;
    public const DATE_OF_BIRTH_ENUM_TOMORROW  = 1;

    /**
     * @inheritDoc
     */
    public function getTranslatedBirthday(ContractUser $user): ?string
    {
        $formatValue = UserValue::getUserValueSettingByName($user, 'user_profile_date_of_birth_format');
        $birthday    = $user?->profile?->birthday;

        if (!is_string($birthday)) {
            return null;
        }

        $time = Carbon::createFromFormat('Y-m-d', $birthday);

        if ($time === false) {
            return $birthday;
        }

        return match ($formatValue) {
            self::DATE_OF_BIRTH_SHOW_DAY_MONTH => $time->translatedFormat('m-d'),
            self::DATE_OF_BIRTH_SHOW_ALL       => $time->translatedFormat('Y-m-d'),
            default                            => null,
        };
    }

    public function formatBirthdayForClient(ContractUser $user): string
    {
        $formatValue = UserValue::getUserValueSettingByName($user, 'user_profile_date_of_birth_format');

        return match ($formatValue) {
            self::DATE_OF_BIRTH_SHOW_ALL       => 'YYYY-MM-DD',
            self::DATE_OF_BIRTH_SHOW_DAY_MONTH => 'MM-DD',
            default                            => null,
        };
    }

    public function getAgeFromBirthday(?string $birthday): ?int
    {
        if (!is_string($birthday)) {
            return null;
        }

        $time = Carbon::parse($birthday)->toDateTimeString();

        if ($time === false) {
            return null;
        }

        $diff = Carbon::make(MetaFox::clientDate())->diff($time);

        return $diff->y;
    }

    public function getCurrentAgeByUser(ContractUser $user): ?int
    {
        $birthdaySetting = UserValue::getUserValueSettingByName($user, 'user_profile_date_of_birth_format');

        if (!in_array($birthdaySetting, [self::DATE_OF_BIRTH_SHOW_ALL, self::DATE_OF_BIRTH_SHOW_AGE])) {
            return null;
        }

        try {
            return $this->getAgeFromBirthday($user?->profile?->birthday);
        } catch (Exception $e) {
            // Just silent.
        }

        return null;
    }

    public function getFormattedUpcomingAgeByUser(ContractUser $user): ?string
    {
        if (!$user instanceof UserModel) {
            return null;
        }

        $age = $this->getCurrentAgeByUser($user);

        if ($age === null) {
            return null;
        }

        $birthday = $user->profile->birthday;
        $newAge   = $age + 1;

        $date = Carbon::parse($birthday)->format('m-d');
        $now  = Carbon::make(MetaFox::clientDate());

        if ($date == $now->format('m-d')) {
            $newAge = $age;
        }

        return __p('user::phrase.years_old', ['year' => $newAge]);
    }

    public function getBirthdayFormats(): array
    {
        return [
            'F j, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y',
        ];
    }

    public function getBirthdayFormatsWithoutYear(): array
    {
        return [
            'F j', 'm-d', 'm/d', 'd/m',
        ];
    }


    public function getFormattedBirthday(ContractUser $context, ContractUser $user): string
    {
        if (!$user instanceof UserModel) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        $birthdaySetting = UserValue::getUserValueSettingByName($user, 'user_profile_date_of_birth_format');
        $birthday        = $user->profile->birthday;

        if ($birthday === null) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        if ($birthdaySetting == self::DATE_OF_BIRTH_DONT_SHOW) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        if ($birthdaySetting == self::DATE_OF_BIRTH_SHOW_AGE) {
            return __p('user::phrase.years_old', ['year' => $this->getCurrentAgeByUser($user)]);
        }

        $date   = Carbon::parse($birthday)->format('m-d');
        $format = match ($birthdaySetting) {
            self::DATE_OF_BIRTH_SHOW_DAY_MONTH => Settings::get('user.user_dob_month_day', 'F j'),
            self::DATE_OF_BIRTH_SHOW_ALL       => Settings::get('user.user_dob_month_day_year', 'F j, Y'),
            default                            => 'F jS'
        };

        $now = Carbon::make(MetaFox::clientDate());

        $userEntity = $user->userEntity()->first();

        if ($date == $now->translatedFormat('m-d')) {
            if ($context->entityId() == $user->entityId()) {
                return __p('user::phrase.today');
            }

            return __p('user::web.value_is_gender_birthday', [
                'value'  => self::DATE_OF_BIRTH_ENUM_TODAY,
                'gender' => $userEntity->possessive_gender,
            ]);
        }

        if ($date == $now->tomorrow()->translatedFormat('m-d') && $context->entityId() != $user->entityId()) {
            return __p('user::web.value_is_gender_birthday', [
                'value'  => self::DATE_OF_BIRTH_ENUM_TOMORROW,
                'gender' => $userEntity->possessive_gender,
            ]);
        }

        return __p('user::phrase.birthday_format', [
            'value' => Carbon::parse($user->profile->birthday)->locale($context->preferredLocale())->format($format),
        ]);
    }
}
