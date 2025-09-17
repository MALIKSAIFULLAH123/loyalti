<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use Foxexpert\Sevent\Models\Sevent as Model;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SiteSettingForm.
 * @property Model $resource
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'sevent';
        $vars   = [
            'sevent.enable_terms',

            'sevent.enable_online',
            'sevent.enable_location',
            'sevent.enable_host',

            'sevent.enable_activity_point',
            'sevent.time_format',
            'sevent.time_zone',
            'sevent.enable_add_calendar'
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this
            ->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic             = $this->addBasic();
        $timezones = \DateTimeZone::listIdentifiers();

        $arr = [];
        foreach ($timezones as $timezone) {
            $arr[] = [
                'label' => $timezone, 
                'value' => $timezone
            ];
        }

        $basic->addFields(
            Builder::choice('sevent.time_zone')
                ->label(__p('sevent::phrase.time_zone'))
                ->marginNormal()
                ->options($arr)
                ->sizeLarge(),
            Builder::choice('sevent.time_format')
                ->label(__p('sevent::web.default_time_format_label'))
                ->options([
                    [
                        'label' => __p('sevent::web.format_12_hour'),
                        'value' => 12,
                    ], [
                        'label' => __p('sevent::web.format_24_hour'),
                        'value' => 24,
                    ],
                ]),
            Builder::checkbox('sevent.enable_add_calendar')
                ->required()
                ->label(__p('sevent::phrase.enable_add_calendar')),

            Builder::checkbox('sevent.enable_online')
                ->required()
                ->label(__p('sevent::phrase.enable_online')),

            Builder::checkbox('sevent.enable_location')
                ->required()
                ->label(__p('sevent::phrase.enable_location')),

            Builder::checkbox('sevent.enable_host')
                ->required()
                ->label(__p('sevent::phrase.enable_host')),

            Builder::checkbox('sevent.enable_terms')
                ->required()
                ->label(__p('sevent::phrase.enable_terms')),
            Builder::checkbox('sevent.enable_activity_point')
                ->required()
                ->label(__p('sevent::phrase.enable_activity_point')),
        );

        $this->addDefaultFooter(true);
    }

    protected function getCategoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }
}
