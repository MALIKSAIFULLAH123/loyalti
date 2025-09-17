<?php

namespace MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin\Form;

use Carbon\Carbon;
use MetaFox\EMoney\Facades\UserBalance;
use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Models\User as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class SearchUserBalanceForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchAdjustmentHistoryForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action('/admincp/emoney/user-balance')
            ->asGet()
            ->acceptPageParams([
                'user_full_name',
                'type',
                'currency',
                'from_date',
                'to_date'
            ])
            ->submitAction('@formAdmin/search/SUBMIT')
            ->setValue([
                'from_date' => null,
                'to_date'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset'])
            ->addFields(
                Builder::text('user_full_name')
                    ->forAdminSearchForm()
                    ->maxLength(255)
                    ->label(__p('ewallet::admin.sender'))
                    ->yup(
                        Yup::string()
                    ),
                Builder::choice('type')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.type'))
                    ->options(UserBalance::getAdjustmentTypeOptions()),
                Builder::choice('currency')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.currency'))
                    ->options($this->getCurrencyOptions()),
                Builder::date('from_date')
                    ->label(__p('core::web.from'))
                    ->startOfDay()
                    ->forAdminSearchForm()
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->maxDate(Carbon::now()->toISOString())
                    ->yup(
                        Yup::date()->nullable()
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                    ),
                Builder::date('to_date')
                    ->label(__p('core::phrase.to_label'))
                    ->endOfDay()
                    ->forAdminSearchForm()
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->maxDate(Carbon::now()->toISOString())
                    ->yup(
                        Yup::date()
                            ->nullable()
                            ->min(['ref' => 'from_date'])
                            ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.to_label')]))
                            ->setError('min', __p('activitypoint::validation.the_end_time_should_be_greater_than_the_start_time'))
                            ->setError(
                                'minDateTime',
                                __p('activitypoint::validation.the_end_time_should_be_greater_than_the_current_time')
                            )
                    ),
                Builder::submit()
                    ->label(__p('core::phrase.search'))
                    ->forAdminSearchForm(),
                Builder::clearSearchForm()
                    ->label(__p('core::phrase.reset'))
                    ->align('center')
                    ->forAdminSearchForm()
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
            'width' => [
                'xs' => '100%',
                'sm' => '50%',
            ],
        ];
    }

    protected function getCurrencyOptions(): array
    {
        return app('currency')->getCurrencyOptions();
    }
}
