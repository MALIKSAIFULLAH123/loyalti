<?php

namespace MetaFox\Translation\Policies;

use MetaFox\Platform\Facades\Settings;

class TranslatePolicy
{
    protected string $type = 'translate';

    public function translate(): bool
    {
        if (!Settings::get('translation.enable_translate', true)) {
            return false;
        }

        return true;
    }
}
