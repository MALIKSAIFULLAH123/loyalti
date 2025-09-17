<?php

namespace MetaFox\Localize\Http\Resources\v1\Currency\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Localize\Models\Currency as Model;
use MetaFox\Platform\Facades\Settings;
use MetaFox\RegexRule\Support\Facades\Regex;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * CurrencyEditForm
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreCurrencyForm.
 * @property ?Model $resource
 * @driverType form
 * @driverName core.currency.store
 */
class StoreCurrencyForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asPost()
            ->title(__p('localize::currency.add_new_currency'))
            ->action(apiUrl('admin.localize.currency.store'))
            ->secondAction('@redirectTo')
            ->setValue([
                'is_active'  => 1,
                'is_default' => 0,
                'format'     => '{0} #,###.00 {1}',
            ]);
    }

    protected function initialize(): void
    {
        $currencyCodeRegex = Regex::getRegexSetting('currency_id');

        $this->addBasic()
            ->addFields(
                Builder::text('name')
                    ->required()
                    ->label(__p('core::phrase.name'))
                    ->yup(Yup::string()->required()->minLength(3)->uppercase()),
                Builder::text('currency_code')
                    ->required()
                    ->label(__p('localize::currency.code'))
                    ->maxLength(3)
                    ->yup(Yup::string()->required(__p('localize::validation.currency_code_is_required_field'))
                        ->maxLength(3, __p('localize::validation.currency_code_maximum_string_length_description', ['min' => 3]))
                        ->minLength(3, __p('localize::validation.currency_code_maximum_string_length_description', ['min' => 3]))
                        ->matches($currencyCodeRegex, __p(Regex::getRegexErrorMessage('currency_id')))),
                Builder::text('symbol')
                    ->required()
                    ->label(__p('localize::currency.symbol'))
                    ->yup(Yup::string()->required()->maxLength(3)),
                Builder::text('format')
                    ->required()
                    ->label(__p('localize::currency.format'))
                    ->placeholder('{0} #,###.00 {1}')
                    ->description(__p('localize::currency.currency_format_description'))
                    ->setAttributes([
                        'alwayShowDescription' => true,
                    ])
                    ->yup(Yup::string()->required()->minLength(8)),
                Builder::checkbox('is_default')
                    ->disabled($this->isDisable())
                    ->label(__p('core::web.default_ucfirst')),
                Builder::checkbox('is_active')
                    ->label(__p('core::phrase.is_active'))
                    ->showWhen([
                        'and',
                        [
                            'eq',
                            'is_default',
                            0,
                        ],
                        [
                            'neq',
                            'is_using',
                            1,
                        ],

                    ]),
            );
        $this->addDefaultFooter();
    }

    protected function isDisable(): bool
    {
        return false;
    }
}
