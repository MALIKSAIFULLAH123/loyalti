<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest\Admin;

use Carbon\Carbon;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\ActivityPoint\Support\Facade\PointConversion;
use MetaFox\ActivityPoint\Models\ConversionRequest as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchConversionRequestForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchConversionRequestForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->asGet()
            ->action('/activitypoint/conversion-request')
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
                    ->placeholder(__p('activitypoint::admin.creator'))
                    ->label(__p('activitypoint::admin.creator')),
                Builder::dropdown('status')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.status'))
                    ->options(PointConversion::getConversionRequestStatusOptions()),
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
}
