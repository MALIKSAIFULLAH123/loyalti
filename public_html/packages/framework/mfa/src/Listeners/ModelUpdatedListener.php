<?php

namespace MetaFox\Mfa\Listeners;

use MetaFox\Core\Models\SiteSetting;
use MetaFox\Mfa\Support\Facades\MfaEnforcer;

class ModelUpdatedListener
{
    public function handle($model): void
    {
        if (!$model instanceof SiteSetting) {
            return;
        }

        if ($model->name != 'mfa.enforce_mfa') {
            return;
        }

        if (!$model->isDirty('value_actual')) {
            return;
        }

        if ($model->value_actual == 1) {
            return;
        }

        MfaEnforcer::onEnforcerDisabled();
    }
}
