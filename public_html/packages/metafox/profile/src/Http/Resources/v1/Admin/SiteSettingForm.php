<?php

namespace MetaFox\Profile\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\Builder;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Facades\Settings;

/**
 | --------------------------------------------------------------------------
 | Form Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'profile';
        $vars   = [

        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic();

        $this->addDefaultFooter(true);
    }
}
