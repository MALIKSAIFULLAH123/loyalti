<?php

namespace MetaFox\Giphy\Http\Resources\v1\Admin;

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
        $module = 'giphy';

        $vars = [
            'giphy_api_key',
        ];

        $value = [];

        foreach ($vars as $var) {
            $var = $module . '.' . $var;
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('giphy.giphy_api_key')
                ->label(__p('giphy::admin.giphy_api_key_label'))
                ->description(__p('giphy::admin.giphy_api_key_desc', ['link' => 'https://developers.giphy.com/docs/api#quick-start-guide']))
                ->required(),
        );

        $this->addDefaultFooter(true);
    }
}
