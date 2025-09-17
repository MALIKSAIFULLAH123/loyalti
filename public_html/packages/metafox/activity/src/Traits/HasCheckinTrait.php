<?php

namespace MetaFox\Activity\Traits;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;

trait HasCheckinTrait
{
    public function isEnableCheckin(): bool
    {
        return Settings::get('activity.feed.enable_check_in', false) === true;
    }

    /**
     * @param array<string, mixed> $rules
     *
     * @return array<string, mixed>
     */
    public function applyLocationRules(array $rules): array
    {
        if ($this->isEnableCheckin()) {
            $rules['location']          = ['sometimes', 'array'];
            $rules['location.full_address']  = ['sometimes', 'nullable', 'string'];
            $rules['location.address']  = ['string'];
            $rules['location.lat']      = ['numeric'];
            $rules['location.lng']      = ['numeric'];
            $rules['location.show_map'] = ['sometimes', new AllowInRule([0, 1])];
        }

        return $rules;
    }
}
