<?php

namespace MetaFox\User\Contracts;

use MetaFox\Platform\Contracts\User as ContractUser;

/**
 * Interface UserBirthday.
 */
interface UserBirthday
{
    /**
     * @param ContractUser $user
     * @return string|null
     */
    public function getTranslatedBirthday(ContractUser $user): ?string;

    /**
     * @param ContractUser $user
     * @return string
     */
    public function formatBirthdayForClient(ContractUser $user): string;

    /**
     * @param string|null $birthday
     * @return int|null
     */
    public function getAgeFromBirthday(?string $birthday): ?int;

    /**
     * @param ContractUser $user
     *
     * @return int|null
     */
    public function getCurrentAgeByUser(ContractUser $user): ?int;

    /**
     * @param ContractUser $user
     *
     * @return string|null
     */
    public function getFormattedUpcomingAgeByUser(ContractUser $user): ?string;

    /**
     * @return array
     */
    public function getBirthdayFormats(): array;

    /**
     * @return array
     */
    public function getBirthdayFormatsWithoutYear(): array;

    /**
     * @param ContractUser $context
     * @param ContractUser $user
     * @return string
     */
    public function getFormattedBirthday(ContractUser $context, ContractUser $user): string;

}
