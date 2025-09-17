<?php

namespace MetaFox\EMoney\Http\Resources\v1\Transaction;

use Carbon\Carbon;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\WithdrawRequest as Model;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchWithdrawRequestForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchTransactionForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asGet()
            ->action('emoney/transaction')
            ->acceptPageParams(['from_date', 'to_date', 'status', 'buyer', 'base_currency', 'source', 'type'])
            ->setValue([
                'from_date' => null,
                'to_date'   => null,
            ]);
    }

    protected function getUserField(): ?AbstractField
    {
        return Builder::text('buyer')
            ->forAdminSearchForm()
            ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
            ->placeholder(__p('ewallet::web.ewallet_user'))
            ->sxFieldWrapper($this->getResponsiveSx())
            ->label(__p('ewallet::web.ewallet_user'));
    }

    protected function getOwnerField(): ?AbstractField
    {
        return null;
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset'])
            ->addFields(
                $this->getUserField(),
                $this->getOwnerField(),
                Builder::choice('base_currency')
                    ->forAdminSearchForm()
                    ->label(__p('ewallet::phrase.base_currency'))
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->options(Emoney::getBaseCurrencyOptions()),
                Builder::choice('source')
                    ->forAdminSearchForm()
                    ->label(__p('ewallet::web.source'))
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->options(Emoney::getSourceOptions()),
                Builder::choice('type')
                    ->forAdminSearchForm()
                    ->label(__p('ewallet::web.action'))
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->options(Emoney::getTypeOptions()),
                Builder::choice('status')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.status'))
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->options(Emoney::getTransactionStatusOptions()),
                Builder::date('from_date')
                    ->forAdminSearchForm()
                    ->label(__p('core::web.from'))
                    ->startOfDay()
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->maxDate(Carbon::now()->toISOString())
                    ->yup(
                        Yup::date()->nullable()
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                    ),
                Builder::date('to_date')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.to_label'))
                    ->endOfDay()
                    ->maxDate(Carbon::now()->toISOString())
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->yup(
                        Yup::date()
                            ->nullable()
                            ->min(['ref' => 'from_date'])
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                            ->setError('min', __p('ewallet::validation.the_end_time_should_be_greater_than_the_start_time'))
                            ->setError(
                                'minDateTime',
                                __p('ewallet::validation.the_end_time_should_be_greater_than_the_current_time')
                            )
                    ),
                Builder::submit()
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.search')),
                Builder::clearSearchForm()
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.reset'))
                    ->align('center')
                    ->sizeMedium(),
            );
    }

    protected function getResponsiveSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '50%',
                'md' => '220px',
            ],
            'width'    => [
                'xs' => '100%',
                'sm' => '50%',
            ],
        ];
    }
}
