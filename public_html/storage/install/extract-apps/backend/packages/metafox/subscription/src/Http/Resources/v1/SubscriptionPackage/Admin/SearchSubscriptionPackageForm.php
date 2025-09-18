<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage\Admin;

use Carbon\Carbon;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use MetaFox\Subscription\Support\Helper;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchSubscriptionPackageForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchSubscriptionPackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/subscription-package')
            ->acceptPageParams(['q', 'status', 'type'])
            ->setValue([
                'payment_statistic_from' => null,
                'payment_statistic_to'   => null,
            ])
            ->submitAction('@formAdmin/search/SUBMIT');
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic([
            'sx' => [
                'flexFlow'   => 'wrap',
                'alignItems' => 'flex-start',
            ],
        ])->asHorizontal()->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm(),
            Builder::choice('type')
                ->forAdminSearchForm()
                ->label(__p('subscription::admin.package_type'))
                ->options($this->getPackageTypes()),
            Builder::choice('status')
                ->forAdminSearchForm()
                ->label(__p('subscription::admin.package_status'))
                ->options($this->getPackageStatuses()),
            Builder::choice('payment_statistic')
                ->forAdminSearchForm()
                ->label(__p('subscription::admin.created_time'))
                ->maxWidth('250px')
                ->options($this->getStatisticOptions()),
            Builder::date('payment_statistic_from')
                ->maxDate(Carbon::now()->toISOString())
                ->startOfDay()
                ->forAdminSearchForm()
                ->label(__p('subscription::admin.from'))
                ->showWhen([
                    'eq',
                    'payment_statistic',
                    Helper::STATISTICS_CUSTOM,
                ])
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->when(
                            Yup::when('payment_statistic')
                                ->is(Helper::STATISTICS_CUSTOM)
                                ->then(
                                    Yup::date()
                                        ->nullable()
                                        ->setError('typeError', __p('validation.date', ['attribute' => __p('subscription::admin.from')]))
                                )
                        )
                ),
            Builder::date('payment_statistic_to')
                ->endOfDay()
                ->maxDate(Carbon::now()->toISOString())
                ->forAdminSearchForm()
                ->label(__p('subscription::admin.to'))
                ->showWhen([
                    'eq',
                    'payment_statistic',
                    Helper::STATISTICS_CUSTOM,
                ])
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->when(
                            Yup::when('payment_statistic')
                                ->is(Helper::STATISTICS_CUSTOM)
                                ->then(
                                    Yup::date()
                                        ->nullable()
                                        ->min(['ref' => 'payment_statistic_from'])
                                        ->setError('typeError', __p('validation.date', ['attribute' => __p('subscription::admin.to')]))
                                        ->setError('min', __p('subscription::admin.date_to_must_be_greater_than_or_equal_to_date_from'))
                                )
                        )
                ),
            Builder::submit()
                ->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->align('center')
                ->forAdminSearchForm()
                ->sizeMedium(),
        );
    }

    protected function getPackageTypes(): array
    {
        return [
            [
                'label' => __p('subscription::admin.one_time'),
                'value' => Helper::PACKAGE_TYPE_ONE_TIME,
            ],
            [
                'label' => __p('subscription::admin.recurring'),
                'value' => Helper::PACKAGE_TYPE_RECURRING,
            ],
        ];
    }

    protected function getPackageStatuses(): array
    {
        return [
            [
                'label' => __p('core::phrase.is_active'),
                'value' => Helper::STATUS_ACTIVE,
            ],
            [
                'label' => __p('subscription::admin.inactive'),
                'value' => Helper::STATUS_DEACTIVE,
            ],
        ];
    }

    protected function getStatisticOptions(): array
    {
        return Helper::getStatisticOptions();
    }
}
