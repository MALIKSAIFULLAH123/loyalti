<?php

namespace MetaFox\Socialite\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class AppleSettingForm.
 * @codeCoverageIgnore
 */
class BaseSettingForm extends Form
{
    public function alertMessage($modified = []): ?string
    {
        if (!Arr::get($modified, 'all')) {
            return null;
        }

        if ($this->shouldAlertToRebuildMobile()) {
            return $this->rebuildMobileMessage();
        }

        return $this->rebuildMessage();
    }
}
