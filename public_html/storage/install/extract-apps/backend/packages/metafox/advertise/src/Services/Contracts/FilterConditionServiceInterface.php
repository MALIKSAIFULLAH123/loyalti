<?php

namespace MetaFox\Advertise\Services\Contracts;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

interface FilterConditionServiceInterface
{
    /**
     * @param  Entity $entity
     * @return bool
     */
    public function filterByDate(Entity $entity): bool;

    /**
     * @param  User   $context
     * @param  Entity $entity
     * @return bool
     */
    public function filterByUserLocation(User $context, Entity $entity): bool;

    /**
     * @param  User   $user
     * @param  Entity $entity
     * @return bool
     */
    public function filterByGender(User $user, Entity $entity): bool;

    /**
     * @param  User   $user
     * @param  Entity $entity
     * @return bool
     */
    public function filterByLanguage(User $user, Entity $entity): bool;

    /**
     * @param  User   $user
     * @param  Entity $entity
     * @return bool
     */
    public function filterByAge(User $user, Entity $entity): bool;

    /**
     * @param  User   $context
     * @param  Entity $entity
     * @return bool
     */
    public function filterByUserInformation(User $context, Entity $entity): bool;

    /**
     * @param  User $context
     * @param  User $owner
     * @return bool
     */
    public function filterBlocked(User $context, ?User $owner): bool;
}
