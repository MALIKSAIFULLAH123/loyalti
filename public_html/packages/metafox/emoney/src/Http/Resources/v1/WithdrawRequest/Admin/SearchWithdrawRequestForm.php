<?php

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawRequest\Admin;

use Carbon\Carbon;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\EMoney\Models\WithdrawRequest as Model;

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
class SearchWithdrawRequestForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asGet()
            ->action('/emoney/request')
            ->acceptPageParams(['creator', 'from_date', 'to_date', 'status'])
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
                Builder::text('creator')
                    ->forAdminSearchForm()
                    ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                    ->placeholder(__p('ewallet::admin.creator'))
                    ->label(__p('ewallet::admin.creator')),
                Builder::dropdown('status')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.status'))
                    ->options(Emoney::getRequestStatusOptions()),
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
                        ->setError('min', __p('ewallet::validation.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError(
                            'minDateTime',
                            __p('ewallet::validation.the_end_time_should_be_greater_than_the_current_time')
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
}
