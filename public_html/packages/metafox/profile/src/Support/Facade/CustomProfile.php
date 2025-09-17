<?php

namespace MetaFox\Profile\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Profile\Contracts\CustomProfileInterface;
use MetaFox\Profile\Models\Section;

/**
 * @method static array getProfileValues(User $user, array $attributes)
 * @method static void saveValues(User $user, array $input, array $attributes)
 * @method static array denormalize(User $user, array $attributes)
 * @method static array handleSectionSystem(User $context, User $resource, Section $section, array $attributes = [])
 */
class CustomProfile extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CustomProfileInterface::class;
    }
}
