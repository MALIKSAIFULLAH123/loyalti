<?php

namespace MetaFox\Featured\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Jobs\HandleInvoiceForDeletedContentJob;
use MetaFox\Featured\Models\Invoice;
use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Models\Package;
use MetaFox\Featured\Notifications\CancelledInvoiceNotification;
use MetaFox\Featured\Notifications\MarkedInvoiceAsPaidNotification;
use MetaFox\Featured\Notifications\SuccessPaymentNotification;
use MetaFox\Featured\Policies\InvoicePolicy;
use MetaFox\Featured\Repositories\InvoiceRepositoryInterface;
use MetaFox\Featured\Repositories\ItemRepositoryInterface;
use MetaFox\Featured\Repositories\PackageRepositoryInterface;
use MetaFox\Featured\Repositories\TransactionRepositoryInterface;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class InvoiceRepository.
 */
class InvoiceRepository extends AbstractRepository implements InvoiceRepositoryInterface
{
    public function model()
    {
        return Invoice::class;
    }

    /**
     * @param User    $user
     * @param Content $content
     * @param array   $attributes
     *
     * @return Invoice
     */
    public function createInvoiceForFree(User $user, Content $content, array $attributes): Invoice
    {
        $userCurrency = Arr::get($attributes, 'currency', app('currency')->getUserCurrencyId($user));

        $attributes = array_merge($attributes, [
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_id'   => $content->entityId(),
            'item_type' => $content->entityType(),
            'status'    => Feature::getCompletedPaymentStatus(),
            'price'     => 0,
            'currency'  => $userCurrency,
        ]);

        /**
         * @var Invoice $invoice
         */
        $invoice = $this->getModel()->newInstance($attributes);

        $invoice->save();

        $invoice->refresh();

        $this->createTransaction($invoice, Feature::getTransactionIdForFree());

        resolve(ItemRepositoryInterface::class)->markItemFree($invoice->featuredItem);

        return $invoice;
    }

    public function createInvoice(User $user, Content $content, array $attributes): Invoice
    {
        /**
         * @var Package $package
         */
        $package = resolve(PackageRepositoryInterface::class)->getPackageById(Arr::get($attributes, 'package_id'));

        if (null === $package) {
            throw new ModelNotFoundException(__p('featured::validation.package_not_found'), 404);
        }

        $userCurrency = Arr::get($attributes, 'currency', app('currency')->getUserCurrencyId($user));

        $price = Arr::get($attributes, 'price', $package->getPriceByCurrency($userCurrency));

        $attributes = array_merge($attributes, [
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_id'   => $content->entityId(),
            'item_type' => $content->entityType(),
            'status'    => Feature::getInitPaymentStatus(),
            'price'     => $price,
            'currency'  => $userCurrency,
        ]);

        /**
         * @var Invoice $invoice
         */
        $invoice = $this->getModel()->newInstance($attributes);

        $invoice->save();

        $invoice->refresh();

        if ($invoice->is_free) {
            $this->handleSuccessPayment($invoice, Feature::getTransactionIdForFree());
        }

        return $invoice->refresh();
    }

    protected function handleSuccessPayment(Invoice $invoice, ?string $transactionId = null, ?int $gatewayId = null): bool
    {
        $invoice->update([
            'status'          => Feature::getCompletedPaymentStatus(),
            'payment_gateway' => $gatewayId,
        ]);

        $this->sendSuccessPaymentNotification($invoice);

        $this->activateItemFeatured($invoice);

        $this->increaseTotalActive($invoice);

        $this->markItemRunning($invoice);

        $this->createTransaction($invoice, $transactionId);

        if ($invoice->is_free) {
            resolve(ItemRepositoryInterface::class)->markItemFree($invoice->featuredItem);
        }

        return true;
    }

    public function updateSuccessPayment(Order $order, ?string $transactionId = null): bool
    {
        if (!$order->item instanceof Invoice) {
            return false;
        }

        $invoice = $order->item;

        return $this->handleSuccessPayment($invoice, $transactionId, $order->gateway_id);
    }

    protected function createTransaction(Invoice $invoice, ?string $transactionId = null): void
    {
        $attributes = [
            'user_id'         => $invoice->userId(),
            'user_type'       => $invoice->userType(),
            'item_id'         => $invoice->itemId(),
            'item_type'       => $invoice->itemType(),
            'invoice_id'      => $invoice->entityId(),
            'status'          => $invoice->status,
            'payment_gateway' => $invoice->payment_gateway,
            'price'           => $invoice->price,
            'currency'        => $invoice->currency,
            'transaction_id'  => $transactionId,
        ];

        resolve(TransactionRepositoryInterface::class)->createTransaction($attributes);
    }

    protected function markItemRunning(Invoice $invoice): void
    {
        if (!$invoice->featuredItem instanceof Item) {
            return;
        }

        resolve(ItemRepositoryInterface::class)->markItemRunning($invoice->featuredItem);
    }

    protected function increaseTotalActive(Invoice $invoice): void
    {
        Feature::increasePackageTotalActive($invoice->package_id);
    }

    protected function activateItemFeatured(Invoice $invoice): void
    {
        Feature::activateItemFeatured($invoice->item);
    }

    protected function sendSuccessPaymentNotification(Invoice $invoice): void
    {
        if (!$invoice->user instanceof User) {
            return;
        }

        $notification = new SuccessPaymentNotification($invoice);

        $params = [$invoice->user, $notification];

        Notification::send(...$params);
    }

    public function updatePendingPayment(Order $order, ?string $transactionId = null): bool
    {
        if (!$order->item instanceof Invoice) {
            return false;
        }

        $invoice = $order->item;

        $invoice->update([
            'status'          => Feature::getPendingPaymentStatus(),
            'payment_gateway' => $order->gateway_id,
        ]);

        $this->createTransaction($invoice, $transactionId);

        if ($invoice->featuredItem instanceof Item) {
            resolve(ItemRepositoryInterface::class)->markItemPendingPayment($invoice->featuredItem);
        }

        return true;
    }

    protected function refreshInvoice(Invoice $invoice): ?Invoice
    {
        $package = $invoice->package;

        if (!$package instanceof Package) {
            return null;
        }

        if (null === $invoice->user) {
            return null;
        }

        $this->cancelOutdatedInvoices($invoice);

        $price = $package->getPriceByCurrency($invoice->currency);

        return $this->createInvoice($invoice->user, $invoice->item, [
            'package_id'  => $invoice->package_id,
            'price'       => $price,
            'currency'    => $invoice->currency,
            'featured_id' => $invoice->featured_id,
        ]);
    }

    public function cancelOutdatedInvoices(Invoice $invoice): void
    {
        $this->getModel()->newQuery()
            ->where([
                'item_id'   => $invoice->item_id,
                'item_type' => $invoice->item_type,
                'user_id'   => $invoice->user_id,
                'status'    => Feature::getInitPaymentStatus(),
            ])
            ->update(['status' => Feature::getCancelledPaymentStatus()]);
    }

    public function payment(Invoice $invoice, int $gatewayId, array $extra = []): ?array
    {
        if (!$invoice->item instanceof Content) {
            return null;
        }

        if (policy_check(InvoicePolicy::class, 'refresh', $invoice)) {
            $invoice = $this->refreshInvoice($invoice);

            /*
             * Refresh with new price but the new price is free.
             */
            if ($invoice->is_completed) {
                return [
                    'status'               => true,
                    'gateway_redirect_url' => $invoice->toLink(),
                ];
            }
        }

        if (null === $invoice) {
            return [
                'success' => false,
            ];
        }

        if (null === $invoice->user) {
            return null;
        }

        if (!policy_check(InvoicePolicy::class, 'payment', $invoice->user, $invoice)) {
            return null;
        }

        $order = Payment::initOrder($invoice);

        if (null === $order) {
            return [];
        }

        $url = $invoice->toUrl();

        $params = [
            'return_url'  => $url,
            'cancel_url'  => $url,
            'description' => __p('featured::phrase.purchase_feature_description', [
                'item_type'  => __p_type_key($invoice->item->entityType()),
                'item_title' => Feature::getItemTitle($invoice->item),
                'site_title' => Settings::get('core.general.site_name', 'MetaFox'),
            ]),
        ];

        if (!empty($extra)) {
            $params = array_merge($params, $extra);
        }

        return Payment::placeOrder($order, $gatewayId, $params);
    }

    public function handleContentDeleted(Content $content): bool
    {
        HandleInvoiceForDeletedContentJob::dispatch($content->entityType(), $content->entityId(), Feature::getItemTitle($content));

        return true;
    }

    /*
     * TODO: Implement if need
     */
    public function handleItemDeleted(Item $item): bool
    {
        return true;
    }

    protected function buildSearchConditions(Builder $builder, array $attributes = []): void
    {
        $id = Arr::get($attributes, 'id');

        $transactionId = Arr::get($attributes, 'transaction_id');

        $fullName = Arr::get($attributes, 'full_name');

        $fromDate = Arr::get($attributes, 'from_date');

        $toDate = Arr::get($attributes, 'to_date');

        $attributes = Arr::only($attributes, ['item_type', 'package_id', 'status', 'payment_gateway']);

        if (count($attributes)) {
            foreach ($attributes as $key => $value) {
                $builder->where(sprintf('featured_invoices.%s', $key), '=', $value);
            }
        }

        if (is_numeric($id)) {
            $builder->where('featured_invoices.id', '=', $id);
        }

        if (is_string($fullName)) {
            $builder->join('user_entities', function (JoinClause $joinClause) {
                $joinClause->on('user_entities.id', '=', 'featured_invoices.user_id');
            })->where('user_entities.name', $this->likeOperator(), '%' . $fullName . '%');
        }

        if (is_string($transactionId)) {
            $builder->join('featured_transactions', function (JoinClause $joinClause) {
                $joinClause->on('featured_transactions.invoice_id', '=', 'featured_invoices.id');
            })->where('featured_transactions.transaction_id', '=', $transactionId);
        }

        $whenScope = new WhenScope();
        $whenScope->setWhen(Browse::WHEN_BETWEEN);

        if (is_string($fromDate)) {
            $whenScope->setFromColumn('featured_invoices.created_at');
            $whenScope->setFromDate($fromDate);
        }

        if (is_string($toDate)) {
            $whenScope->setToColumn('featured_invoices.created_at');
            $whenScope->setToDate($toDate);
        }

        $builder->addScope($whenScope);
    }

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Paginator
     */
    public function viewInvoicesAdminCP(User $context, array $attributes = []): Paginator
    {
        $builder = $this->getModel()->newQuery();

        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $this->buildSearchConditions($builder, $attributes);

        return $builder->with(['userEntity', 'item', 'package', 'paidTransaction', 'paymentGateway'])
            ->orderByDesc('id')
            ->paginate($limit, ['featured_invoices.*']);
    }

    public function viewInvoices(User $context, array $attributes = []): Paginator
    {
        $builder = $this->getModel()->newQuery()
            ->where([
                'featured_invoices.user_id' => $context->entityId(),
            ]);

        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $this->buildSearchConditions($builder, $attributes);

        return $builder->with(['item', 'package', 'paidTransaction', 'paymentGateway'])
            ->orderByDesc('id')
            ->paginate($limit, ['featured_invoices.*']);
    }

    public function cancelInvoice(User $context, Invoice $invoice): bool
    {
        $invoice->update(['status' => Feature::getCancelledPaymentStatus()]);

        $this->sendCancelledNotification($context, $invoice);

        return true;
    }

    protected function sendCancelledNotification(User $context, Invoice $invoice): void
    {
        if ($context->entityId() === $invoice->user_id) {
            return;
        }

        if (!$invoice->user instanceof User) {
            return;
        }

        $notification = new CancelledInvoiceNotification($invoice);

        $notification->setSender($context);

        $params = [$invoice->user, $notification];

        Notification::send(...$params);
    }

    public function markAsPaid(User $context, Invoice $invoice): bool
    {
        $invoice->update(['status' => Feature::getCompletedPaymentStatus()]);

        $this->sendMarkAsPaidNotification($context, $invoice);

        $this->activateItemFeatured($invoice);

        $this->increaseTotalActive($invoice);

        $this->markItemRunning($invoice);

        $this->createTransaction($invoice);

        return true;
    }

    protected function sendMarkAsPaidNotification(User $context, Invoice $invoice): void
    {
        if ($context->entityId() === $invoice->user_id) {
            return;
        }

        if (!$invoice->user instanceof User) {
            return;
        }

        $notification = new MarkedInvoiceAsPaidNotification($invoice);

        $notification->setSender($context);

        $params = [$invoice->user, $notification];

        Notification::send(...$params);
    }

    public function getUnpaidInvoiceForItem(User $user, Item $item): Invoice
    {
        if ($item->unpaidInvoice instanceof Invoice) {
            return $item->unpaidInvoice;
        }

        if (null === $item->item) {
            throw new AuthorizationException();
        }

        return $this->createInvoice($user, $item->item, [
            'package_id'  => $item->package_id,
            'featured_id' => $item->entityId(),
        ]);
    }
}
