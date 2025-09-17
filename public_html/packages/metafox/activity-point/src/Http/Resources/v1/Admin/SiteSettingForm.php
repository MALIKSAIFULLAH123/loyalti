<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'activitypoint';

        $vars   = [
            sprintf('%s.conversion_rate', $module),
            sprintf('%s.conversion_request_fee', $module),
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            $this->buildPricingGroupFields(),
            Builder::text('activitypoint.conversion_request_fee')
                ->label(__p('activitypoint::admin.conversion_request_fee_label'))
                ->description(__p('activitypoint::admin.conversion_request_fee_desc'))
                ->required()
                ->yup(
                    Yup::number()
                        ->required(__p('validation.field_is_a_required_field', [
                            'field' => __p('activitypoint::admin.conversion_request_fee_label'),
                        ]))
                        ->min(0)
                        ->max(100)
                        ->setError('typeError', __p('activitypoint::admin.conversion_fee_format_is_invalid'))
                ),
        );

        $this->addDefaultFooter(true);
    }

    /**
     * @note Temporarily fix to compatible with ticket before core supporting
     * @return AbstractField
     */
    protected function buildPricingGroupFields(): AbstractField
    {
        $field = Builder::currencyPricingGroup('activitypoint.conversion_rate')
            ->label(__p('activitypoint::phrase.conversation_rate_label'))
            ->sx([
                'userSelect' => 'none',
            ]);

        $codes = collect(app('currency')->getAllActiveCurrencies())->keys()->values()->toArray();

        foreach ($codes as $code) {
            $field->addField(
                Builder::text(sprintf('%s.%s', 'activitypoint.conversion_rate', $code))
                    ->label($code)
                    ->required()
                    ->description(__p('activitypoint::admin.conversion_rate_description', ['currency' => $code]))
                    ->startAdornment($code)
                    ->fullWidth()
                    ->preventScrolling()
                    ->asNumber()
                    ->yup(Yup::number()->positive()->required(__p('core::validation.this_field_is_a_required_field')))
            );
        }

        return $field;
    }
}
