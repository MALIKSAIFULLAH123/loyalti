<?php

namespace MetaFox\TourGuide\Http\Resources\v1;

use MetaFox\TourGuide\Policies\TourGuidePolicy;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getWebSettings(): array
    {
        $context = user();

        return [
            'can_create_tourguide' => policy_check(TourGuidePolicy::class, 'create', $context),
        ];
    }
}
