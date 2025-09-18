<?php

namespace MetaFox\Subscription\Support;

use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Contracts\SubscriptionInvoiceContract;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Models\SubscriptionPendingRegistrationUser;
use MetaFox\Subscription\Policies\SubscriptionInvoicePolicy;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPendingRegistrationUserRepositoryInterface;

class SubscriptionInvoice implements SubscriptionInvoiceContract
{
    /**
     * @var SubscriptionInvoiceRepositoryInterface
     */
    protected $repository;

    /**
     * @param SubscriptionInvoiceRepositoryInterface $repository
     */
    public function __construct(SubscriptionInvoiceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function updatePayment(int $id, string $status, array $extra = []): bool
    {
        return $this->repository->updatePayment($id, $status, $extra);
    }

    public function getTransactions(int $id, array $attributes = []): array
    {
        return $this->repository->viewTransactions($id, $attributes);
    }

    public function getTableFields(): array
    {
        return [
            [
                'label'  => __p('subscription::phrase.transaction_date'),
                'value'  => 'created_at',
                'isDate' => true,
            ],
            [
                'label' => __p('subscription::phrase.amount'),
                'value' => 'amount',
            ],
            [
                'label' => __p('subscription::phrase.payment_method'),
                'value' => 'payment_method',
            ],
            [
                'label' => __p('subscription::phrase.id'),
                'value' => 'id',
            ],
            [
                'label' => __p('subscription::phrase.payment_status_label'),
                'value' => 'payment_status',
            ],
        ];
    }

    public function getPaymentButtons(User $context, Model $invoice): array
    {
        $buttons = [];

        if (policy_check(SubscriptionInvoicePolicy::class, 'payNow', $context, $invoice)) {
            $buttons[] = [
                'label' => __p('subscription::phrase.pay_now'),
                'value' => 'subscription/getPayNowSubscriptionForm',
                'color' => 'primary',
            ];
        }

        if (policy_check(SubscriptionInvoicePolicy::class, 'renew', $context, $invoice)) {
            $buttons[] = [
                'label' => __p('subscription::phrase.renew'),
                'value' => 'subscription/getRenewSubscriptionForm',
                'color' => 'primary',
            ];
        }

        if (policy_check(SubscriptionInvoicePolicy::class, 'upgrade', $context, $invoice)) {
            $hasChangeInvoice = policy_check(SubscriptionInvoicePolicy::class, 'changeInvoice', $context, $invoice);

            switch ($hasChangeInvoice) {
                case true:
                    $buttons[] = [
                        'label' => __p('subscription::phrase.upgrade'),
                        'value' => 'subscription/changeInvoice',
                        'color' => 'primary',
                    ];
                    break;
                default:
                    $buttons[] = [
                        'label' => __p('subscription::phrase.upgrade'),
                        'value' => 'subscription/getUpgradeSubscriptionForm',
                        'color' => 'primary',
                    ];
                    break;
            }
        }

        if (policy_check(SubscriptionInvoicePolicy::class, 'cancel', $context, $invoice)) {
            $buttons[] = [
                'label' => __p('subscription::phrase.cancel_subscription'),
                'value' => 'subscription/getCancelSubscriptionForm',
                'color' => 'error',
            ];
        }

        return $buttons;
    }

    public function getReturnUrl(Model $invoice): string
    {
        return $invoice->toUrl();
    }

    public function getCancelUrl(string $location, ?Model $invoice = null): string
    {
        switch ($location) {
            case Helper::CANCELED_URL_LOCATION_INVOICE_DETAIL:
                $url = $this->getReturnUrl($invoice);
                break;
            default:
                $url = url_utility()->makeApiFullUrl('subscription');
                break;
        }

        return $url;
    }

    public function handleRegistration(User $context, int $id): bool
    {
        return $this->repository->handleRegistration($context, $id);
    }

    public function getPendingInvoiceInRegistration(User $user): ?SubscriptionPendingRegistrationUser
    {
        return resolve(SubscriptionPendingRegistrationUserRepositoryInterface::class)->getPendingRegistrationUser($user);
    }

    public function hasPaidInvoices(User $context): bool
    {
        return $this->repository->hasPaidInvoices($context);
    }

    public function checkSubscriptionPackage(SubscriptionPackage $resource): bool
    {
        return $this->repository->getModel()->newModelQuery()
            ->where('package_id', $resource->id)
            ->exists();
    }
}
