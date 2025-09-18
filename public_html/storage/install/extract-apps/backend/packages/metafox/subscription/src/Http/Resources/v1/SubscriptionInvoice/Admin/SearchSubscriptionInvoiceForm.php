<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use MetaFox\Subscription\Repositories\SubscriptionCancelReasonRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
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
class SearchSubscriptionInvoiceForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('subscription::admin.manage_subscriptions'))
            ->noHeader()
            ->action('/subscription-invoice')
            ->acceptPageParams([
                'member_name', 'id', 'package_id',
                'payment_status', 'reason',
                'statistic', 'statistic_from', 'statistic_to',
            ])
            ->submitAction('@formAdmin/search/SUBMIT')
            ->setValue([
                'statistic_from' => null,
                'statistic_to'   => null,
            ]);
    }

    public function initialize(): void
    {
        $this->addBasic()
            ->sxContainer(['alignItems' => 'unset'])
            ->asHorizontal()
            ->addFields(
                Builder::text('id')
                    ->forAdminSearchForm()
                    ->optional()
                    ->label(__p('subscription::admin.invoice_id'))
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->setError('typeError', __p('subscription::admin.invoice_id_must_be_numeric'))
                    ),
                Builder::text('member_name')
                    ->forAdminSearchForm()
                    ->optional()
                    ->label(__p('subscription::admin.member_name')),
                Builder::choice('package_id')
                    ->forAdminSearchForm()
                    ->optional()
                    ->options($this->getPackageOptions())
                    ->label(__p('subscription::admin.package_title')),
                Builder::choice('payment_status')
                    ->forAdminSearchForm()
                    ->optional()
                    ->options($this->getStatusOptions())
                    ->label(__p('subscription::admin.status')),
                Builder::choice('reason')
                    ->forAdminSearchForm()
                    ->optional()
                    ->options($this->getReasonOptions())
                    ->showWhen([
                        'eq',
                        'payment_status',
                        Helper::getCanceledPaymentStatus(),
                    ])
                    ->label(__p('subscription::admin.reason')),
                Builder::choice('statistic')
                    ->label(__p('subscription::admin.cancelled_date'))
                    ->options($this->getStatisticOptions())
                    ->showWhen([
                        'eq',
                        'payment_status',
                        Helper::getCanceledPaymentStatus(),
                    ])
                    ->forAdminSearchForm()
                    ->sizeSmall()
                    ->marginDense()
                    ->multiple(false)
                    ->disableClearable(),
                Builder::date('statistic_from')
                    ->forAdminSearchForm()
                    ->startOfDay()
                    ->sizeSmall()
                    ->label(__p('subscription::admin.from'))
                    ->marginDense()
                    ->showWhen([
                        'eq',
                        'statistic',
                        Helper::STATISTICS_CUSTOM,
                    ])
                    ->yup(
                        Yup::date()
                            ->nullable()
                            ->when(
                                Yup::when('statistic')
                                    ->is(Helper::STATISTICS_CUSTOM)
                                    ->then(
                                        Yup::date()
                                            ->nullable()
                                            ->setError('typeError', __p('subscription::admin.you_must_choose_date_for_from'))
                                    )
                            )
                    ),
                Builder::date('statistic_to')
                    ->forAdminSearchForm()
                    ->endOfDay()
                    ->label(__p('subscription::admin.to'))
                    ->sizeSmall()
                    ->marginDense()
                    ->showWhen([
                        'eq',
                        'statistic',
                        Helper::STATISTICS_CUSTOM,
                    ])
                    ->yup(
                        Yup::date()
                            ->nullable()
                            ->when(
                                Yup::when('statistic')
                                    ->is(Helper::STATISTICS_CUSTOM)
                                    ->then(
                                        Yup::date()
                                            ->nullable()
                                            ->min(['ref' => 'statistic_from'])
                                            ->setError('typeError', __p('subscription::admin.you_must_choose_date_for_to'))
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

    protected function getStatusOptions(): array
    {
        return [
            [
                'label' => __p('subscription::phrase.payment_status.active'),
                'value' => Helper::getCompletedPaymentStatus(),
            ],
            [
                'label' => __p('subscription::phrase.payment_status.cancelled'),
                'value' => Helper::getCanceledPaymentStatus(),
            ],
            [
                'label' => __p('subscription::phrase.payment_status.expired'),
                'value' => Helper::getExpiredPaymentStatus(),
            ],
            [
                'label' => __p('subscription::phrase.payment_status.pending_payment'),
                'value' => Helper::getPendingPaymentStatus(),
            ],
        ];
    }

    protected function getPackageOptions(): array
    {
        $packages = resolve(SubscriptionPackageRepositoryInterface::class)->getActivePackages();

        $options = [];

        if ($packages->count()) {
            foreach ($packages as $package) {
                $options[] = [
                    'label' => $package->toTitle(),
                    'value' => $package->entityId(),
                ];
            }
        }

        return $options;
    }

    public function getStatisticOptions(): array
    {
        return [
            [
                'label' => __p('subscription::admin.all_date'),
                'value' => Helper::STATISTICS_ALL,
            ],
            [
                'label' => __p('subscription::admin.custom_date'),
                'value' => Helper::STATISTICS_CUSTOM,
            ],
        ];
    }

    protected function getReasonOptions(): array
    {
        return resolve(SubscriptionCancelReasonRepositoryInterface::class)->getReasonOptions();
    }
}
