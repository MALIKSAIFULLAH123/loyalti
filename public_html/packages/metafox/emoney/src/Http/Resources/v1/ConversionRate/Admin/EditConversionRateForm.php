<?php

namespace MetaFox\EMoney\Http\Resources\v1\ConversionRate\Admin;

use MetaFox\EMoney\Support\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\EMoney\Models\ConversionRate as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditConversionRateForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditConversionRateForm extends AbstractForm
{
    protected function prepare(): void
    {
        $values = [
            'is_synchronized' => (int) $this->resource->is_synchronized,
        ];

        if (is_numeric($this->resource->exchange_rate)) {
            $values['exchange_rate'] = $this->resource->exchange_rate;
        }

        $this->title(__p('core::phrase.edit'))
            ->action('admincp/emoney/exchange-rate/' . $this->resource->entityId())
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::switch('is_synchronized')
                    ->label(__p('ewallet::admin.auto_synchronization'))
                    ->description(__p('ewallet::admin.auto_synchronized_description')),
                Builder::text('exchange_rate')
                    ->requiredWhen([
                        'falsy', 'is_synchronized',
                    ])
                    ->enableWhen([
                        'falsy', 'is_synchronized',
                    ])
                    ->showWhen([
                        'falsy', 'is_synchronized',
                    ])
                    ->label(__p('ewallet::admin.exchange_rate'))
                    ->description(__p('ewallet::admin.exchange_rate_description', ['number' => Support::MAXIMUM_EXCHANGE_RATE_DECIMAL_PLACE_NUMBER]))
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->when(
                                Yup::when('is_synchronized')
                                    ->is(0)
                                    ->then(
                                        Yup::number()
                                            ->required()
                                            ->min(Support::MINIMUM_EXCHANGE_RATE_NUMBER, __p('ewallet::validation.exchange_rate_min', ['number' => 0]))
                                            ->setError('typeError', __p('ewallet::validation.exchange_rate_format_is_invalid')),
                                    )
                            )
                            ->min(Support::MINIMUM_EXCHANGE_RATE_NUMBER, __p('ewallet::validation.exchange_rate_min', ['number' => 0]))
                            ->setError('typeError', __p('ewallet::validation.exchange_rate_format_is_invalid')),
                    ),
            );

        $this->addDefaultFooter(true);
    }
}
