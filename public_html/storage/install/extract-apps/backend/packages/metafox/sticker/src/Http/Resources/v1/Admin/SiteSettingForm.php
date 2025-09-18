<?php

namespace MetaFox\Sticker\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'sticker';
        $vars   = [
            'sticker.maximum_recent_sticker_can_create',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this
            ->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('sticker.maximum_recent_sticker_can_create')
                ->label(__p('sticker::phrase.maximum_recent_sticker_can_create'))
                ->description(__p('sticker::phrase.maximum_recent_sticker_can_create_description'))
                ->yup(
                    Yup::number()
                        ->int()
                        ->min(1, __p('core::validation.min.numeric', ['attribute' => '${path}', 'min' => 1]))
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
        );

        $this->addDefaultFooter(true);
    }
}
