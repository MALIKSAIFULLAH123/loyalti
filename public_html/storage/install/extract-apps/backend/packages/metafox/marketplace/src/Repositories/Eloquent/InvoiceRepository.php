<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use MetaFox\Marketplace\Models\Invoice;
use MetaFox\Marketplace\Models\InvoiceTransaction;
use MetaFox\Marketplace\Notifications\OwnerPaymentSuccessNotification;
use MetaFox\Marketplace\Notifications\PaymentPendingNotification;
use MetaFox\Marketplace\Notifications\PaymentSuccessNotification;
use MetaFox\Marketplace\Policies\InvoicePolicy;
use MetaFox\Marketplace\Repositories\InvoiceRepositoryInterface;
use MetaFox\Marketplace\Repositories\InvoiceTransactionRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Marketplace\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class InvoiceRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class InvoiceRepository extends AbstractRepository implements InvoiceRepositoryInterface
{
    public function model()
    {
        return Invoice::class;
    }

    public function createInvoice(User $context, int $id, int $gatewayId, array $extra = []): array
    {
        $listing = resolve(ListingRepositoryInterface::class)->find($id);

        policy_authorize(InvoicePolicy::class, 'payment', $context, $listing, $gatewayId);

        $invoice = new Invoice();

        [$price, $currencyId] = ListingFacade::getUserPaymentInformation($context, $listing->price);

        $invoice->fill([
            'listing_id'      => $listing->entityId(),
            'user_id'         => $context->entityId(),
            'user_type'       => $context->entityType(),
            'price'           => $price,
            'currency_id'     => $currencyId,
            'payment_gateway' => $gatewayId,
            'status'          => ListingFacade::getInitPaymentStatus(),
            'paid_at'         => null,
        ]);

        $invoice->save();

        return $this->paymentWithInvoice($invoice, $listing->userId(), $gatewayId, $extra);
    }

    protected function paymentWithInvoice(Invoice $invoice, int $ownerId, int $gatewayId, array $extra = []): array
    {
        $order = Payment::initOrder($invoice);

        if (null === $order) {
            return [];
        }

        $url = $invoice->toUrl();

        $params = [
            'return_url'  => $url,
            'cancel_url'  => $url,
            'payee_id'    => $ownerId,
            'description' => __p('marketplace::phrase.purchase_listing_description', [
                'title'      => $invoice->listing?->toTitle(),
                'site_title' => Settings::get('core.general.site_name', 'MetaFox'),
            ]),
        ];

        if (!empty($extra)) {
            $params = array_merge($params, $extra);
        }

        return Payment::placeOrder($order, $gatewayId, $params);
    }

    public function updateSuccessPayment(int $id, ?string $transactionId = null): void
    {
        $status = ListingFacade::getCompletedPaymentStatus();

        $invoice = $this->updatePaymentStatus($id, $status);

        if (null === $invoice) {
            return;
        }

        $invoice->fill([
            'paid_at' => $invoice->freshTimestamp(),
        ]);

        $invoice->saveQuietly();

        $transaction = resolve(InvoiceTransactionRepositoryInterface::class)->createTransaction([
            'invoice_id'      => $id,
            'status'          => $status,
            'price'           => $invoice->price,
            'currency_id'     => $invoice->currency_id,
            'transaction_id'  => $transactionId,
            'payment_gateway' => $invoice->payment_gateway,
        ]);

        if (null === $transaction) {
            return;
        }

        if (null === $invoice->user) {
            return;
        }

        if (null !== $invoice->listing) {
            resolve(ListingRepositoryInterface::class)->closeListingAfterPayment($invoice->listing_id);

            if (null !== $invoice->listing->user) {
                $this->toOwnerSuccessNotification($invoice->listing->user, $transaction);
            }
        }

        $this->toSuccessNotification($invoice->user, $transaction);
    }

    public function updatePendingPayment(int $id, ?string $transactionId = null): void
    {
        $status = ListingFacade::getPendingPaymentStatus();

        $invoice = $this->updatePaymentStatus($id, $status);

        if (null === $invoice) {
            return;
        }

        $transaction = resolve(InvoiceTransactionRepositoryInterface::class)->createTransaction([
            'invoice_id'      => $id,
            'status'          => $status,
            'price'           => $invoice->price,
            'currency_id'     => $invoice->currency_id,
            'transaction_id'  => $transactionId,
            'payment_gateway' => $invoice->payment_gateway,
        ]);

        if (null === $transaction) {
            return;
        }

        if (null === $invoice->user) {
            return;
        }

        $this->toPendingNotification($invoice->user, $transaction);
    }

    protected function updatePaymentStatus(int $id, string $status): ?Invoice
    {
        $invoice = $this->find($id);

        if ($invoice->status == $status) {
            return null;
        }

        $invoice->fill([
            'status' => $status,
        ]);

        $invoice->saveQuietly();

        return $invoice;
    }

    protected function toPendingNotification(User $user, InvoiceTransaction $transaction)
    {
        $params = [$user, new PaymentPendingNotification($transaction)];

        Notification::send(...$params);
    }

    protected function toSuccessNotification(User $user, InvoiceTransaction $transaction)
    {
        $params = [$user, new PaymentSuccessNotification($transaction)];

        Notification::send(...$params);
    }

    protected function toOwnerSuccessNotification(User $user, InvoiceTransaction $transaction)
    {
        $params = [$user, new OwnerPaymentSuccessNotification($transaction)];

        Notification::send(...$params);
    }

    public function viewInvoices(User $context, array $attributes = []): Paginator
    {
        policy_authorize(InvoicePolicy::class, 'viewAny', $context);

        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->buildQueryViewInvoices($context, $attributes);

        return $query->paginate($limit, ['marketplace_invoices.*']);
    }

    private function buildQueryViewInvoices(User $context, array $attributes): Builder
    {
        $view      = Arr::get($attributes, 'view', ViewScope::VIEW_DEFAULT);
        $status    = Arr::get($attributes, 'status');
        $listingId = Arr::get($attributes, 'listing_id');
        $dateFrom  = Arr::get($attributes, 'from');
        $dateTo    = Arr::get($attributes, 'to');

        $query = $this->getModel()
            ->newModelQuery()
            ->with(['listing']);

        $viewScope = new ViewScope();
        $viewScope->setView($view)->setUserContext($context);
        $query->addScope($viewScope);

        if ($listingId) {
            $query->whereHas('listing', function ($q) use ($listingId) {
                $q->where('marketplace_listings.id', '=', $listingId);
            });
        }

        if ($status) {
            $query->where('marketplace_invoices.status', $status);
        }

        if ($dateFrom) {
            $query->where('marketplace_invoices.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('marketplace_invoices.created_at', '<=', $dateTo);
        }

        $query->orderByRaw(DB::raw('
                CASE
                    WHEN marketplace_invoices.paid_at IS NOT NULL THEN 1
                    ELSE 2
                END ASC
            ')->getValue(DB::getQueryGrammar()))
            ->orderByDesc('marketplace_invoices.created_at');

        return $query;
    }

    public function viewInvoice(User $context, int $id): ?Invoice
    {
        $invoice = $this
            ->with(['transactions'])
            ->find($id);

        policy_authorize(InvoicePolicy::class, 'view', $context, $invoice);

        return $invoice;
    }

    public function changeInvoice(User $context, int $id): ?Invoice
    {
        $invoice = $this->find($id);

        policy_authorize(InvoicePolicy::class, 'change', $context, $invoice);

        $listing = $invoice->listing;

        $price = ListingFacade::getPriceByCurrency($invoice->currency, $listing->price);

        if ($price == $invoice->price) {
            return null;
        }

        $attributes = [
            'listing_id'      => $listing->entityId(),
            'user_id'         => $invoice->userId(),
            'user_type'       => $invoice->userType(),
            'price'           => $price,
            'currency_id'     => $invoice->currency,
            'payment_gateway' => $invoice->payment_gateway,
            'status'          => ListingFacade::getInitPaymentStatus(),
        ];

        $newInvoice = new Invoice();

        $newInvoice->fill($attributes);

        $success = $newInvoice->save();

        if (!$success) {
            return null;
        }

        $oldInvoices = $this->getModel()->newModelQuery()
            ->where([
                'listing_id'  => $listing->entityId(),
                'status'      => ListingFacade::getInitPaymentStatus(),
                'currency_id' => $invoice->currency,
                'user_id'     => $invoice->userId(),
                'user_type'   => $invoice->userType(),
            ])
            ->where('price', '<>', $price)
            ->get();

        foreach ($oldInvoices as $oldInvoice) {
            $oldInvoice->update(['status' => ListingFacade::getCanceledPaymentStatus()]);
        }

        return $newInvoice;
    }

    public function repaymentInvoice(User $context, int $id, int $gatewayId, array $extra = []): array
    {
        $invoice = $this->find($id);

        if (!policy_check(InvoicePolicy::class, 'repayment', $context, $invoice, true)) {
            $response = [
                'status' => false,
            ];

            if (policy_check(InvoicePolicy::class, 'change', $context, $invoice, false)) {
                Arr::set($response, 'error_message', __p('marketplace::phrase.listing_change_price_error'));
            }

            return $response;
        }

        if (null === $invoice->listing) {
            return [];
        }

        if ($invoice->payment_gateway != $gatewayId) {
            $invoice->fill([
                'payment_gateway' => $gatewayId,
            ]);

            $invoice->save();
        }

        return $this->paymentWithInvoice($invoice, $invoice->listing->userId(), $gatewayId, $extra);
    }

    public function getTransactionTableFields(): array
    {
        return [
            [
                'label'  => __p('marketplace::web.transaction_date'),
                'value'  => 'creation_date',
                'isDate' => true,
            ],
            [
                'label' => __p('marketplace::web.amount'),
                'value' => 'price',
            ],
            [
                'label' => __p('marketplace::phrase.payment_method'),
                'value' => 'payment_method',
            ],
            [
                'label' => __p('core::phrase.id'),
                'value' => 'transaction_id',
            ],
            [
                'label' => __p('marketplace::web.payment_status'),
                'value' => 'status',
            ],
        ];
    }
}
