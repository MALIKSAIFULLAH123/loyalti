<?php

namespace MetaFox\Profile\Contracts;

use MetaFox\Platform\Contracts\User;
use MetaFox\Profile\Models\Section;

interface CustomProfileInterface
{

    /**
     * @param User  $user
     * @param array $attributes
     */
    public function getProfileValues(User $user, array $attributes): array;

    /**
     * @param User  $user
     * @param array $attributes
     * @return array
     */
    public function denormalize(User $user, array $attributes): array;

    /**
     * @param User  $user
     * @param array $input
     * @param array $attributes
     * @return void
     */
    public function saveValues(User $user, array $input, array $attributes): void;

    /**
     * @param User    $context
     * @param User    $resource
     * @param Section $section
     * @param array   $attributes
     * @return array
     */
    public function handleSectionSystem(User $context, User $resource, Section $section, array $attributes = []): array;
}
