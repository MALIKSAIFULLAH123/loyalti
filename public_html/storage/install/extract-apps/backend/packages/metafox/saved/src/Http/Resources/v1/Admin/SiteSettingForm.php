<?php

namespace MetaFox\Saved\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
| --------------------------------------------------------------------------
| Form Configuration
| --------------------------------------------------------------------------
| stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub
 */

/**
 * Class SiteSettingForm.
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'saved';
        $vars   = [
            'enable_saved_in_detail',
            'enable_unsaved_confirmation',
        ];

        $value = [];

        foreach ($vars as $var) {
            $var = $module . '.' . $var;
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
            Builder::switch('saved.enable_saved_in_detail')
                ->label(__p('saved::phrase.enable_saved_in_detail_label'))
                ->description(__p('saved::phrase.enable_saved_in_detail_description')),
            Builder::switch('saved.enable_unsaved_confirmation')
                ->label(__p('saved::phrase.enable_unsaved_in_detail_label'))
                ->description(__p('saved::phrase.enable_unsaved_in_detail_description')),
        );

        $this->addDefaultFooter(true);
    }
}
