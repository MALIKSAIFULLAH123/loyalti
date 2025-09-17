<?php

namespace MetaFox\Form;

use MetaFox\Platform\Contracts\User;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;

/**
 * Trait GenderTrait.
 */
trait GenderTrait
{
    /**
     * @param  User                             $context
     * @param  array<string, mixed>|null        $extra
     * @return array<int, array<string, mixed>>
     */
    public function getGenders(User $context, ?array $extra = null): array
    {
        return resolve(UserGenderRepositoryInterface::class)->getForForms($context, $extra);
    }

    /**
     * @param  User                             $context
     * @return array<int, array<string, mixed>>
     */
    public function getDefaultGenders(User $context): array
    {
        return $this->getGenders($context);
    }

    /**
     * @param  User                             $context
     * @return array<int, array<string, mixed>>
     */
    public function getCustomGenders(User $context): array
    {
        return $this->getGenders($context, [
            ['is_custom', '=', 1],
        ]);
    }
}
