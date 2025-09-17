<?php

namespace MetaFox\Advertise\Services;

use Illuminate\Support\Carbon;
use MetaFox\Advertise\Repositories\CountryRepositoryInterface;
use MetaFox\Advertise\Services\Contracts\FilterConditionServiceInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserBlocked;

class FilterConditionService implements FilterConditionServiceInterface
{
    /**
     * @param Entity $entity
     * @return bool
     */
    public function filterByDate(Entity $entity): bool
    {
        if (null === $entity->start_date) {
            return false;
        }

        $startDate = Carbon::parse($entity->start_date);

        $now = Carbon::now();

        if ($startDate->greaterThan($now)) {
            return false;
        }

        if (null === $entity->end_date) {
            return true;
        }

        $endDate = Carbon::parse($entity->end_date);

        if ($endDate->lessThanOrEqualTo($now)) {
            return false;
        }

        return true;
    }

    public function filterByUserLocation(User $context, Entity $entity): bool
    {
        if (!Settings::get('advertise.enable_advanced_filter', false)) {
            return true;
        }

        $locations = resolve(CountryRepositoryInterface::class)->getLocations($entity);

        if (!count($locations)) {
            return true;
        }

        if (!$context instanceof HasUserProfile) {
            return false;
        }

        if (null === $context->profile) {
            return false;
        }

        if (in_array($context->profile->country_iso, $locations)) {
            return true;
        }

        return false;
    }

    public function filterByGender(User $user, Entity $entity): bool
    {
        $ids = $entity->genders()->allRelatedIds()->toArray();

        if (!count($ids)) {
            return true;
        }

        if (!$user instanceof HasUserProfile) {
            return false;
        }

        if (null === $user->profile) {
            return false;
        }

        if (in_array($user->profile->gender_id, $ids)) {
            return true;
        }

        return false;
    }

    public function filterByLanguage(User $user, Entity $entity): bool
    {
        $ids = $entity->languages()->allRelatedIds()->toArray();

        if (!count($ids)) {
            return true;
        }

        if (!$user instanceof HasUserProfile) {
            return false;
        }

        if (null === $user->profile) {
            return false;
        }

        if (null === $user->profile->language_id) {
            return false;
        }

        if (in_array($user->profile->language_id, $ids)) {
            return true;
        }

        return false;
    }

    public function filterByAge(User $user, Entity $entity): bool
    {
        if (!is_numeric($entity->age_from)) {
            return true;
        }

        if (!$user instanceof HasUserProfile) {
            return false;
        }

        if (null === $user->profile) {
            return false;
        }

        $age = UserFacade::getUserAge($user->profile->birthday);

        if (null === $age) {
            return false;
        }

        if (!is_numeric($age)) {
            return false;
        }

        if ($age < $entity->age_from) {
            return false;
        }

        if (!is_numeric($entity->age_to)) {
            return true;
        }

        if ($age > $entity->age_to) {
            return false;
        }

        return true;
    }

    public function filterByUserInformation(User $context, Entity $entity): bool
    {
        if (!$this->filterByGender($context, $entity)) {
            return false;
        }

        if (!$this->filterByLanguage($context, $entity)) {
            return false;
        }

        if (!$this->filterByAge($context, $entity)) {
            return false;
        }

        return true;
    }

    public function filterBlocked(User $context, ?User $owner): bool
    {
        if (null === $owner) {
            return false;
        }

        if (UserBlocked::isBlocked($context, $owner)) {
            return false;
        }

        if (UserBlocked::isBlocked($owner, $context)) {
            return false;
        }

        return true;
    }
}
