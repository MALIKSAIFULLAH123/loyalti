<?php

namespace MetaFox\Core\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Core\Models\SiteSetting as Model;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SpamSettingForm.
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @property Model $resource
 * @driverName core.spam
 */
class SpamSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'core';

        $vars = [
            'core.spam.warning_on_external_links',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.spam_assistance'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::switch('core.spam.warning_on_external_links')
                    ->label(__p('core::phrase.external_links_warning'))
                    ->description(__p('core::phrase.external_links_warning_description'))
            );

        $this->addDefaultFooter(true);
    }
}
