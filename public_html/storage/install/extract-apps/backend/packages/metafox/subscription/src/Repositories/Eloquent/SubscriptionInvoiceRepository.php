<?php

namespace MetaFox\Subscription\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Models\SubscriptionInvoiceTransaction;
use MetaFox\Subscription\Models\SubscriptionPackage as PackageModel;
use MetaFox\Subscription\Models\SubscriptionPendingRegistrationUser;
use MetaFox\Subscription\Notifications\CompletedTransaction;
use MetaFox\Subscription\Notifications\ExpiredTransaction;
use MetaFox\Subscription\Notifications\ManualSubscriptionCancellation;
use MetaFox\Subscription\Notifications\PendingTransaction;
use MetaFox\Subscription\Notifications\SystemSubscriptionCancellation;
use MetaFox\Subscription\Policies\SubscriptionInvoicePolicy;
use MetaFox\Subscription\Policies\SubscriptionPackagePolicy;
use MetaFox\Subscription\Repositories\SubscriptionCancelReasonRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPendingRegistrationUserRepositoryInterface;
use MetaFox\Subscription\Support\Browse\Scopes\SearchScope;
use MetaFox\Subscription\Support\Facade\SubscriptionInvoice as Facade;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use MetaFox\Subscription\Support\Helper;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use RuntimeException;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class SubscriptionInvoiceRepository.
 */
class SubscriptionInvoiceRepository extends AbstractRepository implements SubscriptionInvoiceRepositoryInterface
{
    public function model()
    {
        return SubscriptionInvoice::class;
    }

    protected function getTransactionModel(): SubscriptionInvoiceTransaction
    {
        return new SubscriptionInvoiceTransaction();
    }

    public function getUserActiveSubscription(User $context): ?SubscriptionInvoice
    {
        return $this->getModel()->newModelQuery()
            ->with(['package'])
            ->where([
                'subscription_invoices.user_type'      => $context->entityType(),
                'subscription_invoices.user_id'        => $context->entityId(),
                'subscription_invoices.payment_status' => Helper::getCompletedPaymentStatus(),
            ])
            ->first();
    }

    public function createInvoice(User $context, array $attributes, array $extra = []): array
    {
        $package = resolve(SubscriptionPackageRepositoryInterface::class)->find(Arr::get($attributes, 'id'));

        $forceCreate = Arr::get($attributes, 'force_create', false);

        $isRegistration = Arr::get($attributes, 'is_registration', false);

        if (!$forceCreate) {
            policy_authorize(SubscriptionPackagePolicy::class, 'purchase', $context, $package, $isRegistration);
        }

        $prices = $package->getPrices();

        $userCurrencyId = app('currency')->getUserCurrencyId($context);

        $initialPrice = Arr::get($prices, $userCurrencyId);

        if (null === $initialPrice) {
            abort(400, __p('subscription::validation.bad_request'));
        }

        $renewType = $recurringPrice = null;

        if ($package->is_recurring) {
            $recurringPrice = Arr::get($package->getRecurringPrices(), $userCurrencyId);

            if (null === $recurringPrice) {
                abort(400, __p('subscription::validation.bad_request'));
            }

            $renewType = Arr::get($attributes, 'renew_type');

            if (null === $renewType) {
                abort(400, __p('subscription::validation.bad_request'));
            }
        }

        $paymentGateway = Arr::get($attributes, 'payment_gateway', 0);

        $data = [
            'package_id'      => $package->entityId(),
            'user_id'         => $context->entityId(),
            'user_type'       => $context->entityType(),
            'currency'        => $userCurrencyId,
            'initial_price'   => $initialPrice,
            'recurring_price' => $recurringPrice,
            'renew_type'      => $renewType,
            'payment_status'  => Helper::getInitPaymentStatus(),
            'payment_gateway' => $paymentGateway,
            'created_at'      => $this->getModel()->freshTimestamp(),
        ];

        /**
         * @var SubscriptionInvoice $invoice
         */
        $invoice = $this->getModel()->newInstance($data);

        $invoice->save();

        $invoice->refresh();

        if (SubscriptionPackage::isFreePackageForUser($context, $package)) {
            $this->updatePayment($invoice->entityId(), Helper::getCompletedPaymentStatus(), [
                'transaction_id' => $this->getDefaultTransactionId(),
                'total_paid'     => 0,
            ]);

            return [
                'is_free' => true,
            ];
        }

        if (
            SubscriptionPackage::isFirstFreeAndRecurringForUser(
                $context,
                $package
            ) && Helper::RENEW_TYPE_MANUAL == $renewType
        ) {
            $this->updatePayment($invoice->entityId(), Helper::getCompletedPaymentStatus(), [
                'transaction_id' => $this->getDefaultTransactionId(),
                'total_paid'     => 0,
            ]);

            return [
                'is_first_free'        => true,
                'gateway_redirect_url' => url_utility()->makeApiUrl('/subscription/' . $invoice->entityId()),
                'message'              => __p('subscription::phrase.your_subscription_has_been_activated'),
            ];
        }

        $order = Payment::initOrder($invoice);

        $userPendingInvoice = Facade::getPendingInvoiceInRegistration($context);

        if (null !== $userPendingInvoice && $userPendingInvoice->invoice_id != $invoice->entityId()) {
            $userPendingInvoice->update([
                'invoice_id' => $invoice->entityId(),
            ]);
        }

        if (!Arr::get($attributes, 'delay_order')) {
            $params = [
                'return_url'  => Facade::getReturnUrl($invoice),
                'cancel_url'  => Facade::getCancelUrl(Helper::CANCELED_URL_LOCATION_HOME),
                'description' => $invoice->payment_description,
            ];

            if (!empty($extra)) {
                $params = array_merge($params, $extra);
            }

            return Payment::placeOrder($order, $paymentGateway, $params);
        }

        if (Arr::get($attributes, 'get_invoice')) {
            return [
                'invoice' => $invoice,
            ];
        }

        return [
            'order_id' => $order->entityId(),
        ];
    }

    public function updatePayment(int $id, string $status, array $extra = []): bool
    {
        $invoice = $this
            ->with(['package'])
            ->find($id);

        $package = $invoice->package;

        if (null === $package) {
            return false;
        }

        $price = Arr::get($extra, 'total_paid');

        $currency = Arr::get($extra, 'currency');

        $transactionId = Arr::get($extra, 'transaction_id');

        $user = $invoice->user;

        if (null === $user) {
            return false;
        }

        if (null === $transactionId) {
            $transactionId = $this->getDefaultTransactionId();
        }

        $success = match ($status) {
            Helper::getCompletedPaymentStatus() => $this->handleCompleted($invoice, $price, $transactionId, true, $currency),
            Helper::getCanceledPaymentStatus()  => $this->handleCanceled(
                $user,
                $invoice,
                Arr::get($extra, 'is_manual', true),
                Arr::get($extra, 'reason_id')
            ),
            Helper::getExpiredPaymentStatus() => $this->handleExpired($user, $invoice),
            Helper::getPendingPaymentStatus() => $this->handlePending($invoice)
        };

        if ($success) {
            resolve(SubscriptionPackageRepositoryInterface::class)->clearCaches();
        }

        return $success;
    }

    protected function handlePending(SubscriptionInvoice $invoice): bool
    {
        $package = $invoice->package;

        if (null === $package) {
            return false;
        }

        if (!$invoice->update(['payment_status' => Helper::getPendingPaymentStatus()])) {
            return false;
        }

        resolve(SubscriptionPackageRepositoryInterface::class)->updateTotalItem(
            $package->entityId(),
            Helper::getPendingPaymentStatus(),
            Helper::ACTION_PLUS
        );

        $pendingRegistrationUser = resolve(SubscriptionPendingRegistrationUserRepositoryInterface::class)
            ->getPendingRegistrationUser($invoice->user, $invoice->entityId());

        if ($pendingRegistrationUser instanceof SubscriptionPendingRegistrationUser) {
            return true;
        }

        $notificationParams = [$invoice->user, new PendingTransaction($invoice)];
        Notification::send(...$notificationParams);

        return true;
    }

    protected function handleCompleted(SubscriptionInvoice $invoice, ?float $price = null, ?string $transactionId = null, bool $isCompletedYourself = true, ?string $currency = null): bool
    {
        $package = $invoice->package;

        if (null === $package) {
            throw new RuntimeException('Subscription Package not found.');
        }
        $currentStatus = $invoice->payment_status;

        if (!$invoice->update(['payment_status' => Helper::getCompletedPaymentStatus()])) {
            return false;
        }

        app('events')->dispatch('invite.user_register_update_status', [$invoice->user], true);

        $packageRepository = resolve(SubscriptionPackageRepositoryInterface::class);

        $upgradedRoleId = $package->upgraded_role_id;

        $availableRoles = Arr::pluck($packageRepository->getRoleOptions(), 'value');

        if (!in_array($upgradedRoleId, $availableRoles)) {
            abort(403);
        }

        $user = $invoice->user;

        $this->updateUserRole($user, $upgradedRoleId);

        //TODO: update subscription flag when registration

        $currentActiveSubscriptions = $this->getModel()->newModelQuery()
            ->where([
                'user_type'      => $user->entityType(),
                'user_id'        => $user->entityId(),
                'payment_status' => Helper::getCompletedPaymentStatus(),
            ])
            ->where('id', '<>', $invoice->entityId())
            ->get();

        if (null !== $currentActiveSubscriptions) {
            $reasonRepository = resolve(SubscriptionCancelReasonRepositoryInterface::class);

            foreach ($currentActiveSubscriptions as $currentActiveSubscription) {
                $currentActiveSubscription->fill([
                    'payment_status' => Helper::getCanceledPaymentStatus(),
                ]);

                $currentActiveSubscription->save();

                $reasonRepository->createUserCancelReason($user, $currentActiveSubscription->entityId());

                $this->createTransaction(
                    $user,
                    $currentActiveSubscription->entityId(),
                    Helper::getCanceledPaymentStatus()
                );
            }
        }

        $update = [];

        if (null == $invoice->activated_at) {
            Arr::set($update, 'activated_at', $this->getModel()->freshTimestamp());
        }

        if (null !== $invoice->renew_type) {
            $periodDate = $this->getRecurringPeriod($package->recurring_period, $invoice->expired_at);

            Arr::set($update, 'expired_at', $periodDate);

            if ($invoice->renew_type == Helper::RENEW_TYPE_MANUAL) {
                $notifiedDate = clone $periodDate;

                $notifiedDate = $notifiedDate->addDays(-1 * (int) $package->days_notification_before_subscription_expired);

                Arr::set($update, 'notified_at', $notifiedDate);
            }
        }

        if (count($update)) {
            $invoice->update($update);
        }

        $transaction = $this->createTransaction(
            $user,
            $invoice->entityId(),
            Helper::getCompletedPaymentStatus(),
            $price,
            $transactionId,
            $currency
        );

        if (null !== $transaction) {
            $notification = new CompletedTransaction($transaction);

            $notification->setIsFirstTransaction($invoice->load('transactions')->transactions()->count() == 1);
            $notification->setIsYourself($isCompletedYourself);

            $notificationParams = [$invoice->user, $notification];

            Notification::send(...$notificationParams);
        }

        if ($currentStatus != Helper::getCompletedPaymentStatus()) {
            resolve(SubscriptionPackageRepositoryInterface::class)->updateTotalItem(
                $package->entityId(),
                Helper::getCompletedPaymentStatus(),
                Helper::ACTION_PLUS
            );
            resolve(SubscriptionPendingRegistrationUserRepositoryInterface::class)->deletePendingRegistrationUser($user);
        }

        return true;
    }

    protected function getRecurringPeriod(string $period, ?string $now = null): Carbon
    {
        $date = Carbon::parse($now);

        switch ($period) {
            case Helper::RECURRING_PERIOD_MONTHLY:
                $date->addMonth();
                break;
            case Helper::RECURRING_PERIOD_QUARTERLY:
                $date->addMonths(3);
                break;
            case Helper::RECURRING_PERIOD_BIANNUALLY:
                $date->addMonths(6);
                break;
            case Helper::RECURRING_PERIOD_ANNUALLY:
                $date->addYear();
                break;
        }

        return $date;
    }

    protected function handleCanceled(
        User $context,
        SubscriptionInvoice $invoice,
        bool $isManual = true,
        ?int $reasonId = null
    ): bool {
        return match ($isManual) {
            true  => $this->handleCanceledByManual($context, $invoice, $reasonId),
            false => $this->handleCanceledByGateway($invoice)
        };
    }

    protected function handleCanceledByManual(User $context, SubscriptionInvoice $invoice, ?int $reasonId = null): bool
    {
        $success = $invoice->update(['payment_status' => Helper::getCanceledPaymentStatus()]);

        if (!$success) {
            return false;
        }

        $this->downgradedUserRole($context, $invoice);

        $this->handleNotificationCancelledByManual($context, $invoice);

        $this->updateTotalItemPackageWhenCancelled($invoice->package);

        if (
            $invoice->is_recurring
            && $invoice->renew_type == Helper::RENEW_TYPE_AUTO
            && null !== $invoice->order
            && null !== $invoice->order->gateway_subscription_id
            && !$invoice->is_canceled_by_gateway
        ) {
            //Cancel recurring in gateway
            Payment::cancelSubscription($invoice->order);
        }

        resolve(SubscriptionCancelReasonRepositoryInterface::class)->createUserCancelReason(
            $context,
            $invoice->entityId(),
            $reasonId
        );

        return true;
    }

    protected function handleNotificationCancelledByManual(User $context, SubscriptionInvoice $invoice): void
    {
        $transaction = $this->createTransaction($context, $invoice->entityId(), Helper::getCanceledPaymentStatus());

        if (null !== $transaction) {
            $notification = new ManualSubscriptionCancellation($transaction);
            $notification->setIsYourself($invoice->userId() == $context->entityId());
            $notificationParams = [$invoice->user, $notification];
            Notification::send(...$notificationParams);
        }
    }

    protected function updateTotalItemPackageWhenCancelled(PackageModel $package): void
    {
        //Increase total_canceled of Package
        resolve(SubscriptionPackageRepositoryInterface::class)->updateTotalItem(
            $package->entityId(),
            Helper::getCanceledPaymentStatus(),
            Helper::ACTION_PLUS
        );

        //Decrease total_active of Package
        resolve(SubscriptionPackageRepositoryInterface::class)->updateTotalItem(
            $package->entityId(),
            Helper::getCompletedPaymentStatus(),
            Helper::ACTION_SUBTRACT
        );

        Cache::forget(Helper::ALL_PACKAGE_CACHE_ID);
    }

    protected function downgradedUserRole(User $context, SubscriptionInvoice $invoice): bool
    {
        $package = $invoice->package;

        $hasDowngradedPackage = null !== $package && null !== $package->downgradedPackage && null !== $package->downgradedPackage->dependencyPackage;

        switch ($hasDowngradedPackage) {
            case true:
                $downgradedPackage = $package->downgradedPackage->dependencyPackage;

                $response = $this->createInvoice($context, [
                    'id'              => $downgradedPackage->entityId(),
                    'force_create'    => true,
                    'renew_type'      => $downgradedPackage->is_recurring ? Helper::RENEW_TYPE_MANUAL : null,
                    'delay_order'     => true,
                    'payment_gateway' => $invoice->payment_gateway,
                ]);

                if (!count($response)) {
                    return false;
                }

                $updateToDefaultUserRole = !Arr::get($response, 'is_free') && !Arr::get($response, 'is_first_free');

                break;
            default:
                $updateToDefaultUserRole = true;
                break;
        }

        if ($updateToDefaultUserRole) {
            $this->updateUserRole(
                $context,
                Settings::get('subscription.default_downgraded_user_role', UserRole::NORMAL_USER_ID)
            );
        }

        return true;
    }

    protected function handleCanceledByGateway(SubscriptionInvoice $invoice): bool
    {
        if (!$invoice->is_canceled_by_gateway) {
            $invoice->update([
                'is_canceled_by_gateway' => true,
            ]);

            $notification = [$invoice->user, new SystemSubscriptionCancellation($invoice)];

            Notification::send(...$notification);
        }

        return true;
    }

    protected function handleExpired(User $context, SubscriptionInvoice $invoice): bool
    {
        $this->downgradedUserRole($context, $invoice);

        $invoice->update(['payment_status' => Helper::getExpiredPaymentStatus()]);

        if (null !== $invoice->package) {
            $repository = resolve(SubscriptionPackageRepositoryInterface::class);

            //Increase total_expired of Package
            $repository->updateTotalItem(
                $invoice->package->entityId(),
                Helper::getExpiredPaymentStatus(),
                Helper::ACTION_PLUS
            );

            //Decrease total_active of Package
            $repository->updateTotalItem(
                $invoice->package->entityId(),
                Helper::getCompletedPaymentStatus(),
                Helper::ACTION_SUBTRACT
            );
        }

        $notification = [$context, new ExpiredTransaction($invoice)];

        Notification::send(...$notification);

        return true;
    }

    protected function updateUserRole(User $user, int $roleId): void
    {
        $userId = $user->entityId();

        if ($userId > 0 && $roleId > 0) {
            $userRole = resolve(RoleRepositoryInterface::class)->roleOf($user);

            $currentRoleId = $userRole->entityId();

            $repository = resolve(UserRepositoryInterface::class);

            $repository->removeRole($userId, $currentRoleId);

            $repository->assignRole($userId, $roleId);
        }
    }

    /**
     * @param int $size
     *
     * @return string
     */
    protected function getDefaultTransactionId(int $size = 17): string
    {
        // $size = 17 is paypal transaction. We refer it to generate default transaction id
        $alpha = '';

        $keys = range('A', 'Z');

        for ($i = 0; $i < 2; $i++) {
            $alpha .= $keys[array_rand($keys)];
        }

        $length = $size - 2;

        $number = '';

        $keys = range(0, 9);

        for ($i = 0; $i < $length; $i++) {
            $number .= $keys[array_rand($keys)];
        }

        return $alpha . $number;
    }

    public function createTransaction(
        User $context,
        int $invoiceId,
        string $paymentStatus,
        ?float $price = null,
        ?string $transactionId = null,
        ?string $currency = null
    ): ?SubscriptionInvoiceTransaction {
        $invoice = $this->find($invoiceId);

        policy_authorize(SubscriptionInvoicePolicy::class, 'createTransaction', $context);

        return $this->createSimpleTransaction($invoice, $paymentStatus, $price, $transactionId, $currency);
    }

    public function createSimpleTransaction(SubscriptionInvoice $invoice, string $paymentStatus, ?float $price = null, ?string $transactionId = null, ?string $currency = null): SubscriptionInvoiceTransaction
    {
        $model = $this->getTransactionModel();

        $paymentType = $invoice->getPaymentType();

        if ($invoice->is_recurring && $invoice->renew_type == Helper::RENEW_TYPE_MANUAL) {
            $paymentType = Helper::getPaymentTypeOnetime();
        }

        $data = [
            'invoice_id'      => $invoice->entityId(),
            'payment_status'  => $paymentStatus,
            'currency'        => $currency ? Str::upper($currency) : $invoice->currency,
            'payment_type'    => $paymentType,
            'payment_gateway' => $invoice->payment_gateway,
            'transaction_id'  => null,
            'paid_price'      => null,
            'created_at'      => $this->getModel()->freshTimestamp(),
        ];

        if (null !== $price) {
            Arr::set($data, 'paid_price', $price);
        }

        if (null !== $transactionId) {
            Arr::set($data, 'transaction_id', $transactionId);
        }

        $model->fill($data);

        $model->save();

        //Remove transactions cache of Invoice
        Cache::deleteMultiple([Helper::ALL_TRANSACTION_CACHE_ID . '_' . $invoice->entityId()]);

        return $model;
    }

    public function viewInvoices(User $context, array $attributes): Paginator
    {
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $view = Arr::get($attributes, 'view', Helper::VIEW_FILTER);

        $query = $this->getModel()->newModelQuery();

        match ($view) {
            Helper::VIEW_FILTER => $query->where([
                'subscription_invoices.user_id'   => $context->entityId(),
                'subscription_invoices.user_type' => $context->entityType(),
            ])
        };

        return $query
            ->with(['package'])
            ->orderByDesc('subscription_invoices.created_at')
            ->simplePaginate($limit);
    }

    /**
     * @throws AuthorizationException
     */
    public function viewInvoice(User $context, int $id): SubscriptionInvoice
    {
        $invoice = $this->with(['package', 'user'])
            ->find($id);

        policy_authorize(SubscriptionInvoicePolicy::class, 'view', $context, $invoice);

        return $invoice;
    }

    public function viewTransactions(int $invoiceId, array $attributes): array
    {
        $view = Arr::get($attributes, 'view', Helper::VIEW_FILTER);

        if ($view == Helper::VIEW_FILTER) {
            return Cache::remember(
                Helper::DEFAULT_CACHE_TTL,
                Helper::ALL_TRANSACTION_CACHE_ID . '_' . $invoiceId,
                function () use ($invoiceId) {
                    $query = $this->getTransactionModel()->newModelQuery()
                        ->where([
                            'invoice_id' => $invoiceId,
                        ]);

                    $transactions = $query->orderByDesc('created_at')
                        ->with(['gateway'])
                        ->get();

                    if (!$transactions->count()) {
                        return [];
                    }

                    $parsed = [];

                    foreach ($transactions as $transaction) {
                        $amount = null;

                        if (null !== $transaction->paid_price) {
                            $amount = app('currency')->getPriceFormatByCurrencyId(
                                $transaction->currency,
                                $transaction->paid_price
                            );
                        }

                        $parsed[] = [
                            'created_at'     => Carbon::parse($transaction->created_at)->format('c'),
                            'amount'         => $amount,
                            'payment_method' => null !== $transaction->gateway ? $transaction->gateway->title : null,
                            'id'             => $transaction->transaction_id,
                            'payment_status' => Helper::getTransactionPaymentStatus($transaction->payment_status),
                        ];
                    }

                    return $parsed;
                }
            );
        }

        return [];
    }

    public function cancelSubscriptionByUser(User $context, int $id, array $attributes = []): bool
    {
        $invoice = $this->find($id);

        policy_authorize(SubscriptionInvoicePolicy::class, 'cancel', $context, $invoice);

        return $this->updatePayment($id, Helper::getCanceledPaymentStatus(), [
            'is_manual' => true,
            'reason_id' => Arr::get($attributes, 'reason_id'),
        ]);
    }

    public function renewInvoice(User $context, int $id, array $attributes, array $extra = []): array
    {
        /**
         * @var SubscriptionInvoice $invoice
         */
        $invoice = $this->find($id);

        policy_authorize(SubscriptionInvoicePolicy::class, 'renew', $context, $invoice);

        $order  = Payment::initOrder($invoice);
        $params = [
            'return_url'  => Facade::getReturnUrl($invoice),
            'cancel_url'  => Facade::getCancelUrl(Helper::CANCELED_URL_LOCATION_INVOICE_DETAIL, $invoice),
            'description' => $invoice->payment_description,
        ];

        if (!empty($extra)) {
            $params = array_merge($params, $extra);
        }

        return Payment::placeOrder($order, Arr::get($attributes, 'payment_gateway'), $params);
    }

    public function changeInvoice(User $context, int $id): ?SubscriptionInvoice
    {
        $invoice = $this->find($id);

        policy_authorize(SubscriptionInvoicePolicy::class, 'changeInvoice', $context, $invoice);

        $package = $invoice->package;

        if (null === $package) {
            return null;
        }

        $currency = $invoice->currency;

        $currentPackagePrice = Arr::get($package->getPrices(), $currency);

        $currentPackageRecurringPrice = Arr::get($package->getRecurringPrices(), $currency);

        if (null === $currentPackagePrice && null === $currentPackageRecurringPrice) {
            return null;
        }

        $attributes = [
            'id'              => $package->entityId(),
            'payment_gateway' => $invoice->payment_gateway,
            'delay_order'     => true,
            'get_invoice'     => true,
        ];

        if ($invoice->is_recurring) {
            Arr::set($attributes, 'renew_type', $invoice->renew_type);
        }

        $result = $this->createInvoice($context, $attributes);

        $newInvoice = Arr::get($result, 'invoice');

        if (null === $newInvoice) {
            return null;
        }

        $query = $this->getModel()->newModelQuery()
            ->where('package_id', '=', $package->entityId())
            ->where('id', '<>', $newInvoice->entityId())
            ->where('payment_status', '=', Helper::getInitPaymentStatus())
            ->where(function ($query) use ($currentPackagePrice, $currentPackageRecurringPrice, $package) {
                $query->where('initial_price', '<>', $currentPackagePrice);

                switch ($package->is_recurring) {
                    case true:
                        $query->orWhereNull('renew_type')
                            ->orWhere('recurring_price', '<>', $currentPackageRecurringPrice);
                        break;
                    default:
                        $query->orWhereNotNull('renew_type')
                            ->orWhereNotNull('recurring_price');
                        break;
                }
            });

        $invoices = $query->get();

        if ($invoices->count()) {
            foreach ($invoices as $invoice) {
                $invoice->update(['payment_status' => Helper::getCanceledPaymentStatus()]);
            }
        }

        return $newInvoice;
    }

    public function upgrade(User $context, int $id, array $attributes, array $extra = []): ?array
    {
        $invoice = $this->find($id);

        $actionType = Arr::get($attributes, 'action_type');

        return match ($actionType) {
            Helper::UPGRADE_FORM_ACTION => $this->upgradeInvoice($context, $invoice, $attributes, $extra),
            Helper::PAY_NOW_FORM_ACTION => $this->payNowInvoice($context, $invoice, $attributes, $extra),
        };
    }

    protected function upgradeInvoice(User $context, SubscriptionInvoice $invoice, array $attributes, array $extra = []): ?array
    {
        policy_authorize(SubscriptionInvoicePolicy::class, 'upgrade', $context, $invoice);

        $order = $invoice->order;

        if (null === $order) {
            return null;
        }

        $paymentGateway = Arr::get($attributes, 'payment_gateway');

        $renewType = Arr::get($attributes, 'renew_type');

        $update = [];

        if ($paymentGateway != $invoice->payment_gateway) {
            Arr::set($update, 'payment_gateway', $paymentGateway);
        }

        if ($invoice->is_recurring) {
            if ($renewType !== $invoice->renew_type) {
                Arr::set($update, 'renew_type', $renewType);
            }

            $newPaymentType = $renewType == Helper::RENEW_TYPE_MANUAL ? Helper::getPaymentTypeOnetime() : Helper::getPaymentTypRecurring();

            if ($newPaymentType != $order->payment_type) {
                $order->update(['payment_type' => $newPaymentType]);
                $order->refresh();
            }
        }

        if (count($update)) {
            $invoice->update($update);
            $invoice->refresh();
        }

        if ($invoice->renew_type == Helper::RENEW_TYPE_MANUAL && $invoice->initial_price == '0.00' && $invoice->isPendingAction()) {
            $this->updatePayment($invoice->entityId(), Helper::getCompletedPaymentStatus(), [
                'transaction_id' => $this->getDefaultTransactionId(),
                'total_paid'     => 0,
            ]);

            $order->delete();

            return [
                'is_free' => true,
            ];
        }

        $order->fill([
            'status' => Helper::getInitPaymentStatus(),
        ]);

        $order->save();

        $params = [
            'return_url'  => Facade::getReturnUrl($invoice),
            'cancel_url'  => Facade::getCancelUrl(Helper::CANCELED_URL_LOCATION_INVOICE_DETAIL, $invoice),
            'description' => $invoice->payment_description,
        ];

        if (!empty($extra)) {
            $params = array_merge($params, $extra);
        }

        return Payment::placeOrder($order, $paymentGateway, $params);
    }

    protected function payNowInvoice(User $context, SubscriptionInvoice $invoice, array $attributes, array $extra = []): ?array
    {
        policy_authorize(SubscriptionInvoicePolicy::class, 'payNow', $context, $invoice);

        $order = $invoice->order;

        if (null === $order) {
            return null;
        }

        $paymentGateway = Arr::get($attributes, 'payment_gateway');

        if ($invoice->payment_gateway != $paymentGateway) {
            $invoice->update(['payment_gateway' => $paymentGateway]);
        }

        $order->update(['status' => Helper::getInitPaymentStatus()]);

        $order->refresh();

        $params = [
            'return_url'  => Facade::getReturnUrl($invoice),
            'cancel_url'  => Facade::getCancelUrl(Helper::CANCELED_URL_LOCATION_INVOICE_DETAIL, $invoice),
            'description' => $invoice->payment_description,
        ];

        if (!empty($extra)) {
            $params = array_merge($params, $extra);
        }

        return Payment::placeOrder($order, $paymentGateway, $params);
    }

    public function viewInvoicesInAdminCP(User $context, array $attributes = []): Paginator
    {
        $memberName = Arr::get($attributes, 'member_name');

        $id = Arr::get($attributes, 'id');

        $packageId = Arr::get($attributes, 'package_id');

        $paymentStatus = Arr::get($attributes, 'payment_status');

        $defaultStatuses = Helper::getPaymentStatusForSearching();

        $sortType = Arr::get($attributes, 'sort_type');

        $sort = Arr::get($attributes, 'sort');

        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        if (is_string($paymentStatus) && $paymentStatus != '') {
            $defaultStatuses = [$paymentStatus];
        }

        $query = $this->getModel()->newModelQuery()
            ->whereIn('subscription_invoices.payment_status', $defaultStatuses);

        if (is_numeric($id)) {
            $query->where('subscription_invoices.id', '=', $id);
        }

        if (is_string($packageId) && $packageId != '') {
            $searchTitleScope = new SearchScope();

            $searchTitleScope->setSearchText($packageId)
                ->setFields(['subscription_packages.id'])
                ->setJoinedTable('subscription_packages')
                ->setJoinedField('id')
                ->setTableField('package_id');

            $query = $query->addScope($searchTitleScope);
        }

        if (is_string($memberName) && $memberName != '') {
            $searchUserScope = new SearchScope();

            $searchUserScope->setSearchText($memberName)
                ->setFields(['users.full_name', 'users.user_name'])
                ->setJoinedTable('users')
                ->setJoinedField('id')
                ->setTableField('user_id');

            $query->addScope($searchUserScope);
        }

        $this->buildQueryForCanceledPaymentStatus($query, $attributes);

        if ($sort && $sortType) {
            $this->buildSort($query, $sort, $sortType);
        }

        return $query
            ->with(['package', 'user'])
            ->orderBy('subscription_invoices.id')
            ->paginate($limit, ['subscription_invoices.*']);
    }

    protected function buildSort(Builder $query, string $sort, string $sortType): void
    {
        if ($sort === 'subscription_user_cancel_reasons.created_at') {
            $query->leftJoin('subscription_user_cancel_reasons', function (JoinClause $clause) {
                $clause->on('subscription_user_cancel_reasons.invoice_id', '=', 'subscription_invoices.id');
            })
            ->orderByRaw('(CASE WHEN subscription_user_cancel_reasons.created_at IS NULL THEN 0 ELSE 1 END) ' . $sortType)
            ->orderBy('subscription_user_cancel_reasons.created_at', $sortType);

            return;
        }

        if ($sort === 'subscription_invoices.expired_at') {
            $query->orderByRaw('(CASE WHEN subscription_invoices.expired_at IS NULL THEN 0 ELSE 1 END) ' . $sortType)
                ->orderBy('subscription_invoices.expired_at', $sortType);
        }

        $query->orderBy($sort, $sortType);
    }

    protected function buildQueryForCanceledPaymentStatus(Builder $query, array $attributes): void
    {
        $paymentStatus = Arr::get($attributes, 'payment_status');

        if ($paymentStatus !== Helper::getCanceledPaymentStatus()) {
            return;
        }

        $this->applyCancelReasonFilter($query, $attributes);
        $this->applyStatisticFilter($query, $attributes);
    }

    protected function applyCancelReasonFilter(Builder $query, array $attributes): void
    {
        $reason    = Arr::get($attributes, 'reason');
        $statistic = Arr::get($attributes, 'statistic');

        if (!$reason && $statistic !== Helper::STATISTICS_CUSTOM) {
            return;
        }

        $query->join('subscription_user_cancel_reasons AS sucr', function (JoinClause $join) {
            $join->on('sucr.invoice_id', '=', 'subscription_invoices.id');
        });

        if ($reason) {
            $query->where('sucr.reason_id', '=', $reason);
        }
    }

    protected function applyStatisticFilter(Builder $query, array $attributes): void
    {
        $statistic = Arr::get($attributes, 'statistic');

        if ($statistic !== Helper::STATISTICS_CUSTOM) {
            return;
        }

        $from = Arr::get($attributes, 'statistic_from');
        $to   = Arr::get($attributes, 'statistic_to');

        if ($from != null) {
            $from = Carbon::parse($from)->format($this->getModel()->getDateFormat());

            $query->where('sucr.created_at', '>=', $from);
        }

        if ($to != null) {
            $to = Carbon::parse($to)->format($this->getModel()->getDateFormat());

            $query->where('sucr.created_at', '<=', $to);
        }
    }

    public function updatePaymentForAdminCP(User $context, int $id, string $status): bool
    {
        $invoice = $this->find($id);

        policy_authorize(SubscriptionInvoicePolicy::class, 'updatePaymentStatusAdminCP', $context, $invoice, $status);

        return match ($status) {
            Helper::getCompletedPaymentStatus() => $this->handleCompletedAdminCP($invoice, $invoice->payment_status),
            Helper::getCanceledPaymentStatus()  => $this->handleCanceledAdminCP($invoice),
        };
    }

    protected function handleCompletedAdminCP(SubscriptionInvoice $invoice, string $oldStatus): bool
    {
        if (!$this->handleCompleted($invoice, null, null, false)) {
            return false;
        }

        if ($invoice->is_recurring) {
            $update = [
                'expired_at' => $this->getRecurringPeriod($invoice->package->recurring_period),
            ];

            if ($oldStatus == Helper::getCanceledPaymentStatus()) {
                $update = array_merge($update, [
                    'renew_type'             => Helper::RENEW_TYPE_MANUAL,
                    'is_canceled_by_gateway' => false,
                ]);
            }

            $invoice->update($update);
        }

        match ($oldStatus) {
            Helper::getCanceledPaymentStatus() => $this->handleFromCanceledToCompleted($invoice),
            Helper::getPendingPaymentStatus()  => $this->handleFromPendingToCompleted($invoice)
        };

        return true;
    }

    protected function handleFromCanceledToCompleted(SubscriptionInvoice $invoice): void
    {
        $invoice->userCanceledReason()->delete();

        if (null !== $invoice->package) {
            resolve(SubscriptionPackageRepositoryInterface::class)->updateTotalItem(
                $invoice->package->entityId(),
                Helper::getCanceledPaymentStatus(),
                Helper::ACTION_SUBTRACT
            );
        }
    }

    protected function handleFromPendingToCompleted(SubscriptionInvoice $invoice): void
    {
        if (null !== $invoice->package) {
            resolve(SubscriptionPackageRepositoryInterface::class)->updateTotalItem(
                $invoice->package->entityId(),
                Helper::getPendingPaymentStatus(),
                Helper::ACTION_SUBTRACT
            );
        }
    }

    protected function handleCanceledAdminCP(SubscriptionInvoice $invoice): bool
    {
        if (null !== $invoice->user) {
            return $this->handleCanceled($invoice->user, $invoice);
        }

        return $this->handleCancelBySystem($invoice);
    }

    public function getExpiredSubscriptions(int $packageId, bool $canceledByGateway = false): Collection
    {
        $query = $this->getModel()->newModelQuery()
            ->where('package_id', '=', $packageId);

        $expiredDate = Carbon::now();

        switch ($canceledByGateway) {
            case true:
                $query->where([
                    'renew_type'             => Helper::RENEW_TYPE_AUTO,
                    'is_canceled_by_gateway' => true,
                ]);

                break;
            default:
                $defaultAddonDays = Settings::get(
                    'subscription.default_addon_expired_day',
                    Helper::DEFAULT_EXPIRED_ADDON_DAY
                ) * -1;

                $expiredDate->addDays($defaultAddonDays);

                $query->where('renew_type', '=', Helper::RENEW_TYPE_MANUAL);

                break;
        }

        $expiredDate = $expiredDate->format('Y-m-d H:i:s');

        return $query
            ->where('payment_status', '=', Helper::getCompletedPaymentStatus())
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', $expiredDate)
            ->get();
    }

    public function getNotifiedInvoices(int $packageId): Collection
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        return $this->getModel()->newModelQuery()
            ->where('package_id', '=', $packageId)
            ->where('payment_status', '=', Helper::getCompletedPaymentStatus())
            ->where('renew_type', '=', Helper::RENEW_TYPE_MANUAL)
            ->whereNotNull('notified_at')
            ->where('notified_at', '<=', $now)
            ->get();
    }

    public function getCanceledSubscriptionsByGateway(int $packageId): Collection
    {
        return $this->getExpiredSubscriptions($packageId, true);
    }

    public function handleRegistration(User $context, int $id): bool
    {
        $package = \MetaFox\Subscription\Models\SubscriptionPackage::query()
            ->where('id', '=', $id)
            ->first();

        if (!$package instanceof \MetaFox\Subscription\Models\SubscriptionPackage) {
            return false;
        }

        $result = resolve(SubscriptionInvoiceRepositoryInterface::class)->createInvoice($context, [
            'id'              => $package->entityId(),
            'delay_order'     => true,
            'get_invoice'     => true,
            'is_registration' => true,
            'renew_type'      => Helper::RENEW_TYPE_MANUAL,
        ]);

        $invoice = Arr::get($result, 'invoice');

        if (null !== $invoice) {
            resolve(SubscriptionPendingRegistrationUserRepositoryInterface::class)->createPendingRegistrationUser(
                $context,
                $invoice->entityId()
            );
        }

        return true;
    }

    public function hasPaidInvoices(User $context): bool
    {
        $total = $this->getModel()->newModelQuery()
            ->where('user_id', $context->entityId())
            ->whereIn('payment_status', [
                Helper::getCompletedPaymentStatus(), Helper::getCanceledPaymentStatus(),
                Helper::getPendingPaymentStatus(), Helper::getExpiredPaymentStatus(),
            ])
            ->count();

        return $total > 0;
    }

    public function getStatisticsByPaymentStatus(
        array $packageIds,
        string $status,
        string $fromDate,
        string $toDate
    ): array {
        $statistics = DB::table('subscription_invoices')
            ->join('subscription_packages', function (JoinClause $joinClause) use ($packageIds) {
                $joinClause->on('subscription_packages.id', '=', 'subscription_invoices.package_id')
                    ->whereIn('subscription_packages.id', $packageIds);
            })
            ->where('subscription_invoices.payment_status', '=', $status)
            ->whereBetween('subscription_invoices.activated_at', [$fromDate, $toDate])
            ->groupBy('subscription_invoices.package_id')
            ->selectRaw(DB::raw('COUNT(*) AS total, subscription_invoices.package_id'))
            ->get();

        $response = [];

        if ($statistics->count()) {
            $statistics = $statistics->toArray();
            $response   = array_combine(array_column($statistics, 'package_id'), array_column($statistics, 'total'));
        }

        return $response;
    }

    public function viewTransactionsInAdminCP(User $context, int $invoiceId): Collection
    {
        return SubscriptionInvoiceTransaction::query()
            ->with(['gateway'])
            ->join('subscription_invoices', function (JoinClause $joinClause) {
                $joinClause->on('subscription_invoices.id', '=', 'subscription_invoice_transactions.invoice_id');
            })
            ->where('subscription_invoice_transactions.invoice_id', $invoiceId)
            ->orderByDesc('subscription_invoice_transactions.id')
            ->get(['subscription_invoice_transactions.*']);
    }

    /**
     * @param User                $context
     * @param SubscriptionInvoice $invoice
     *
     * @return bool
     */
    public function cancelSubscriptionByDowngrade(User $context, SubscriptionInvoice $invoice): bool
    {
        $success = $invoice->update(['payment_status' => Helper::getCanceledPaymentStatus()]);

        if (!$success) {
            return false;
        }

        $this->handleNotificationCancelledByManual($context, $invoice);

        $this->updateTotalItemPackageWhenCancelled($invoice->package);

        resolve(SubscriptionCancelReasonRepositoryInterface::class)->createCancelReason($invoice->entityId());

        $this->createSimpleTransaction($invoice, Helper::getCanceledPaymentStatus());

        return true;
    }

    public function hasCompletedTransactions(int $id): bool
    {
        $count = $this->getTransactionModel()->newModelQuery()
            ->where([
                'invoice_id'     => $id,
                'payment_status' => Helper::getCompletedPaymentStatus(),
            ])
            ->count();

        return $count > 0;
    }

    public function recycleInvoice(SubscriptionInvoice $invoice, ?float $total = null, ?string $transactionId = null)
    {
        $package = $invoice->package;

        if (null === $package) {
            throw new RuntimeException('Subscription Package not found.');
        }

        if (null === $invoice->user) {
            throw new RuntimeException('User not found.');
        }

        $invoice->update(['expired_at' => $this->getRecurringPeriod($package->recurring_period, $invoice->expired_at)]);

        $transaction = $this->createTransaction(
            $invoice->user,
            $invoice->entityId(),
            Helper::getCompletedPaymentStatus(),
            $total ?? $invoice->recurring_price,
            $transactionId
        );

        if (null !== $transaction) {
            $notification = new CompletedTransaction($transaction);

            $notification->setIsFirstTransaction($invoice->load('transactions')->transactions()->count() == 1);

            $notificationParams = [$invoice->user, $notification];

            Notification::send(...$notificationParams);
        }

        return true;
    }

    public function cancelAllUserActiveSubscriptions(User $user): bool
    {
        $this->getModel()->newModelQuery()
            ->where([
                'user_type'      => $user->entityType(),
                'user_id'        => $user->entityId(),
                'payment_status' => Helper::getCompletedPaymentStatus(),
            ])
            ->get()
            ->each(function ($subscription) use ($user) {
                $this->handleCancelBySystem($subscription);
            });

        return true;
    }

    /**
     * @param SubscriptionInvoice $subscription
     *
     * @return bool
     */
    private function handleCancelBySystem(SubscriptionInvoice $subscription): bool
    {
        $subscription->update([
            'payment_status' => Helper::getCanceledPaymentStatus(),
        ]);

        resolve(SubscriptionCancelReasonRepositoryInterface::class)->createCancelReason($subscription->entityId());

        $this->createSimpleTransaction($subscription, Helper::getCanceledPaymentStatus());

        if (null !== $subscription->package) {
            $this->updateTotalItemPackageWhenCancelled($subscription->package);
        }

        return true;
    }
}
