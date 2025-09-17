<?php

namespace MetaFox\Notification\Http\Resources\v1\Admin;

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
        $module = 'notification';
        $vars   = [
            'notification.refresh_time',
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
            Builder::text('notification.refresh_time')
                ->asNumber()
                ->label(__p('notification::admin.how_long_will_the_notice_be_refreshed_minutes'))
                ->description(__p('notification::admin.how_long_will_the_notice_be_refreshed_minutes_desc'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->int()
                        ->min(0)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
        );
        $this->addDefaultFooter(true);
    }
}
