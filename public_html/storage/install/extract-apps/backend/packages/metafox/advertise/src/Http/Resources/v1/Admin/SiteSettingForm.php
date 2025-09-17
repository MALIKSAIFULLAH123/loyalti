<?php

namespace MetaFox\Advertise\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
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
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'advertise';

        $vars = [
            'enable_advertise',
            'enable_advanced_filter',
            'maximum_number_of_advertises_on_side_block',
            'delay_time_to_count_sponsor_view',
            'purchase_sponsorship_after_creating_an_item',
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
        $this->addBasic()
            ->addFields(
                Builder::switch('advertise.enable_advertise')
                    ->label(__p('advertise::admin.enable_advertises_label'))
                    ->description(__p('advertise::admin.enable_advertises_desc')),
                Builder::switch('advertise.enable_advanced_filter')
                    ->label(__p('advertise::admin.enable_advanced_filter_label'))
                    ->description(__p('advertise::admin.enable_advanced_filter_desc')),
                Builder::switch('advertise.purchase_sponsorship_after_creating_an_item')
                    ->label(__p('advertise::admin.purchase_sponsorship_after_creating_an_item_label'))
                    ->description(__p('advertise::admin.purchase_sponsorship_after_creating_an_item_desc')),
                Builder::text('advertise.maximum_number_of_advertises_on_side_block')
                    ->label(__p('advertise::admin.maximum_number_of_advertises_on_side_block_label'))
                    ->description(__p('advertise::admin.maximum_number_of_advertises_on_side_block_desc'))
                    ->yup(
                        Yup::number()
                            ->required()
                            ->min(1)
                            ->setError('typeError', __p('core::phrase.this_field_must_be_number'))
                    ),
                Builder::text('advertise.delay_time_to_count_sponsor_view')
                    ->label(__p('advertise::admin.delay_time_to_count_sponsor_view_label'))
                    ->description(__p('advertise::admin.delay_time_to_count_sponsor_view_desc'))
                    ->yup(
                        Yup::number()
                            ->required()
                            ->min(1)
                            ->setError('typeError', __p('core::phrase.this_field_must_be_number'))
                    ),
            );

        $this->addDefaultFooter(true);
    }
}
