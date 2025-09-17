<?php

namespace MetaFox\User\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\User\Contracts\UserBirthday as UserBirthdayContracts;

/**
 * Interface UserBirthday.
 * @method static int|null          getAgeFromBirthday(?string $birthday)
 * @method static string|null       getTranslatedBirthday(ContractUser $user)
 * @method static string|null       formatBirthdayForClient(ContractUser $user)
 * @method static string|null       getFormattedUpcomingAgeByUser(ContractUser $user)
 * @method static int|null          getCurrentAgeByUser(ContractUser $user)
 * @method static array             getBirthdayFormats()
 * @method static array             getBirthdayFormatsWithoutYear()
 * @method static string            getFormattedBirthday(ContractUser $context, ContractUser $user)
 *
 */
class UserBirthday extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UserBirthdayContracts::class;
    }
}
