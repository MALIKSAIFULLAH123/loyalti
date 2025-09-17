<?php

namespace MetaFox\EMoney\Http\Resources\v1\ConversionRate\Admin;

use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\ConversionRate as Model;
use MetaFox\EMoney\Support\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchConversionRateForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchConversionRateForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asGet()
            ->action('emoney/exchange-rate')
            ->acceptPageParams(['target'])
            ->setValue([
                'target' => Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset'])
            ->addFields(
                Builder::dropdown('target')
                    ->forAdminSearchForm()
                    ->label(__p('ewallet::admin.target'))
                    ->options(Emoney::getBaseCurrencyOptions()),
                Builder::submit()
                    ->label(__p('core::phrase.search'))
                    ->forAdminSearchForm(),
            );
    }
}
