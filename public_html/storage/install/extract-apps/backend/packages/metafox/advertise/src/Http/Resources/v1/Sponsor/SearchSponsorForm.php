<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor;

use Illuminate\Support\Arr;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Advertise\Models\Advertise as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchAdvertiseForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchSponsorForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('advertise/sponsor')
            ->acceptPageParams(['start_date', 'end_date', 'status'])
            ->setValue([
                'start_date' => null,
                'end_date'   => null,
                'status'     => null,
            ])
            ->asGet();
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset'])
            ->addFields(
                Builder::date('start_date')
                    ->label(__p('core::web.from'))
                    ->startOfDay()
                    ->forAdminSearchForm()
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->yup(
                        Yup::date()->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::web.from')]))
                    ),
                Builder::date('end_date')
                    ->label(__p('advertise::phrase.to_ucfirst'))
                    ->endOfDay()
                    ->forAdminSearchForm()
                    ->sxFieldWrapper($this->getResponsiveSx())
                    ->yup(Yup::date()
                        ->nullable()
                        ->min(['ref' => 'start_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('advertise::phrase.to_ucfirst')]))
                        ->setError('min', __p('advertise::phrase.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError(
                            'minDateTime',
                            __p('advertise::phrase.the_end_time_should_be_greater_than_the_current_time')
                        )),
                Builder::choice('status')
                    ->label(__p('core::web.status'))
                    ->options($this->getStatusOptions())
                    ->forAdminSearchForm()
                    ->sxFieldWrapper($this->getResponsiveSx()),
                Builder::submit()
                    ->label(__p('core::phrase.submit'))
                    ->forAdminSearchForm(),
                Builder::clearSearchForm()
                    ->marginDense()
                    ->label(__p('core::phrase.reset')),
            );
    }

    protected function getStatusOptions(): array
    {
        $options = Support::getAdvertiseStatusOptions();

        if (!count($options)) {
            return [];
        }

        Arr::prepend($options, [
            'label' => __p('advertise::phrase.all_status'),
            'value' => null,
        ]);

        return $options;
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
