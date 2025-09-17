<?php

namespace MetaFox\Advertise\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use MetaFox\Advertise\Contracts\AdvertisePaymentInterface;
use MetaFox\Advertise\Models\Invoice;
use MetaFox\Advertise\Models\InvoiceTransaction;
use MetaFox\Advertise\Notifications\OwnerPaymentSuccessNotification;
use MetaFox\Advertise\Policies\InvoicePolicy;
use MetaFox\Advertise\Repositories\InvoiceRepositoryInterface;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function createInvoiceAdminCP(User $context, Entity $entity): Invoice
    {
        $attributes = [
            'user_id'         => $context->entityId(),
            'user_type'       => $context->entityType(),
            'item_id'         => $entity->entityId(),
            'item_type'       => $entity->entityType(),
            'price'           => 0,
            'currency_id'     => app('currency')->getUserCurrencyId($context),
            'payment_gateway' => 0,
            'payment_status'  => Facade::getCompletedPaymentStatus(),
            'paid_at'         => Carbon::now(),
        ];

        $invoice = $this->getModel()->newModelInstance($attributes);

        $invoice->save();

        $invoice->refresh();

        $this->createTransaction([
            'invoice_id'     => $invoice->entityId(),
            'status'         => Facade::getCompletedPaymentStatus(),
            'price'          => 0,
            'currency_id'    => Arr::get($attributes, 'currency_id'),
            'transaction_id' => null,
        ]);

        if (null !== $invoice->item) {
            $invoice->item->toCompletedPayment($invoice);
            $this->cancelUnusedInvoices($invoice);
        }

        $invoice->refresh();

        return $invoice;
    }

    public function createInvoice(User $context, Entity $entity, array $attributes): array
    {
        $policy = PolicyGate::getPolicyFor(get_class($entity));

        if (null === $policy) {
            throw new NotFoundHttpException(__p('advertise::phrase.cant_create_invoice'));
        }

        policy_authorize(get_class($policy), 'payment', $context, $entity);

        $attributes = array_merge($attributes, [
            'user_id'        => $context->entityId(),
            'user_type'      => $context->entityType(),
            'item_id'        => $entity->entityId(),
            'item_type'      => $entity->entityType(),
            'payment_status' => Facade::getPendingActionStatus(),
        ]);

        $price = Arr::get($attributes, 'price');

        if (null === $price) {
            abort(404, __p('advertise::phrase.cant_create_invoice'));
        }

        $isFree = $price == 0;

        if ($isFree) {
            $attributes = array_merge($attributes, [
                'payment_status' => Facade::getCompletedPaymentStatus(),
                'paid_at'        => Carbon::now(),
            ]);
        }

        $invoice = $this->getModel()->newModelInstance($attributes);

        $invoice->save();

        $invoice->refresh();

        if ($isFree) {
            $this->createTransaction([
                'invoice_id'     => $invoice->entityId(),
                'status'         => Facade::getCompletedPaymentStatus(),
                'price'          => $price,
                'currency_id'    => Arr::get($attributes, 'currency_id'),
                'transaction_id' => null,
            ]);

            if (null !== $invoice->item) {
                $invoice->item->toCompletedPayment($invoice);
                $this->cancelUnusedInvoices($invoice);
            }

            return [
                'success' => true,
                'model'   => $invoice,
            ];
        }

        if (Arr::has($attributes, 'delay_payment')) {
            return [
                'model' => $invoice,
            ];
        }

        $extra = $this->getExtraPaymentWithInvoice($attributes);

        return $this->paymentWithInvoice($invoice, Arr::get($attributes, 'payment_gateway'), $extra);
    }

    protected function cancelUnusedInvoices(Invoice $keptInvoice): void
    {
        Invoice::query()
            ->where([
                'item_id'        => $keptInvoice->item->entityId(),
                'item_type'      => $keptInvoice->item->entityType(),
                'payment_status' => Facade::getPendingActionStatus(),
            ])
            ->where('id', '<>', $keptInvoice->entityId())
            ->update([
                'payment_status' => Facade::getCancelledPaymentStatus(),
            ]);
    }

    public function getRefreshInfo(User $context, int $itemId, string $itemType): ?array
    {
        $model = Facade::getMorphedModel($itemId, $itemType);

        if (null === $model) {
            return null;
        }

        $data = $model->toPayment($context);

        if (!count($data)) {
            return null;
        }

        return $data;
    }

    protected function refreshInvoice(User $context, int $itemId, string $itemType): ?Invoice
    {
        $data = $this->getRefreshInfo($context, $itemId, $itemType);

        if (null === $data) {
            return null;
        }

        $model = Facade::getMorphedModel($itemId, $itemType);

        if (null === $model) {
            return null;
        }

        $data = array_merge($data, [
            'item_id'        => $model->entityId(),
            'item_type'      => $model->entityType(),
            'user_id'        => $context->entityId(),
            'user_type'      => $context->entityType(),
            'payment_status' => Facade::getPendingActionStatus(),
        ]);

        $invoice = $this->getModel()->newModelInstance($data);

        $invoice->save();

        $invoice->refresh();

        $this->cancelUnusedInvoices($invoice);

        return $invoice;
    }

    public function paymentInvoice(User $context, int $itemId, string $itemType, int $gatewayId, ?int $id = null, array $extra = []): array
    {
        $invoice = match ($id) {
            null    => $this->refreshInvoice($context, $itemId, $itemType),
            default => $this
                ->with(['item'])
                ->find($id),
        };

        if (null === $invoice) {
            return [
                'success' => false,
            ];
        }

        policy_authorize(InvoicePolicy::class, 'payment', $context, $invoice);

        if ($invoice->item->isPriceChanged($invoice)) {
            return [
                'success' => false,
            ];
        }

        if ($invoice->price == 0) {
            $invoice->update([
                'payment_status' => Facade::getCompletedPaymentStatus(),
                'paid_at'        => Carbon::now(),
            ]);

            $this->createTransaction([
                'invoice_id'     => $invoice->entityId(),
                'status'         => Facade::getCompletedPaymentStatus(),
                'price'          => $invoice->price,
                'currency_id'    => $invoice->currency_id,
                'transaction_id' => null,
            ]);

            return [
                'success'              => true,
                'message'              => __p('advertise::phrase.invoice_successfully_paid'),
                'gateway_redirect_url' => $invoice->toUrl(),
            ];
        }

        $extra = $this->getExtraPaymentWithInvoice($extra);

        return $this->paymentWithInvoice($invoice, $gatewayId, $extra);
    }

    protected function paymentWithInvoice(Invoice $invoice, int $gatewayId, array $extra = []): array
    {
        $order = Payment::initOrder($invoice);

        if (null === $order) {
            return [];
        }

        $invoice->update(['payment_gateway' => $gatewayId]);

        $url = $invoice->toUrl();

        $item = $invoice->item;

        $description = null;

        if ($item instanceof AdvertisePaymentInterface) {
            if (method_exists($item, 'toPaymentReturnUrl')) {
                $url = call_user_func([$item, 'toPaymentReturnUrl']);
            }

            $description = $item->toPaymentDescription();
        }

        $params = [
            'return_url'  => $url,
            'cancel_url'  => $url,
            'description' => $description,
        ];

        if (!empty($extra)) {
            $params = array_merge($params, $extra);
        }

        $result = Payment::placeOrder($order, $gatewayId, $params);

        $invoice->refresh();

        if (null === $item) {
            return $result;
        }

        if (!$invoice->is_completed) {
            return $result;
        }

        return array_merge([
            'success' => true,
        ], $result);
    }

    public function updateSuccessPayment(int $id, ?string $transactionId): void
    {
        $invoice = $this->find($id);

        $invoice->update([
            'payment_status' => Facade::getCompletedPaymentStatus(),
            'paid_at'        => Carbon::now(),
        ]);

        $transaction = $this->createTransaction([
            'invoice_id'     => $invoice->entityId(),
            'status'         => Facade::getCompletedPaymentStatus(),
            'price'          => $invoice->price,
            'currency_id'    => $invoice->currency_id,
            'transaction_id' => $transactionId,
        ]);

        if (null !== $invoice->item) {
            $invoice->item->toCompletedPayment($invoice);

            $invoice->item->refresh();

            if ($invoice->item->status == Support::ADVERTISE_STATUS_APPROVED && $invoice->price > 0) {
                $this->toOwnerSuccessNotification($invoice->user, $transaction);
            }
        }
    }

    public function updatePendingPayment(int $id, ?string $transactionId): void
    {
        $invoice = $this->find($id);

        $invoice->update([
            'payment_status' => Facade::getPendingPaymentStatus(),
        ]);

        $this->createTransaction([
            'invoice_id'     => $invoice->entityId(),
            'status'         => Facade::getPendingPaymentStatus(),
            'price'          => $invoice->price,
            'currency_id'    => $invoice->currency_id,
            'transaction_id' => $transactionId,
        ]);
    }

    public function viewInvoices(User $context, array $attributes = []): Paginator
    {
        policy_authorize(InvoicePolicy::class, 'viewAny', $context);

        $startDate = Arr::get($attributes, 'start_date');
        $endDate   = Arr::get($attributes, 'end_date');
        $status    = Arr::get($attributes, 'status');

        $query = Invoice::query()
            ->with(['completedTransaction', 'item'])
            ->where([
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
            ]);

        if ($startDate || $endDate) {
            $query->whereNotNull('advertise_invoices.paid_at');
        }

        if ($startDate) {
            $query->where('advertise_invoices.paid_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('advertise_invoices.paid_at', '<=', $endDate);
        }

        if ($status) {
            $query->where('advertise_invoices.payment_status', '=', $status);
        }

        return $query->orderByDesc('advertise_invoices.id')
            ->paginate(Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE), ['advertise_invoices.*']);
    }

    public function cancelInvoice(User $context, int $id): Invoice
    {
        $invoice = $this->find($id);

        policy_authorize(InvoicePolicy::class, 'cancel', $context, $invoice);

        $invoice->update(['payment_status' => Facade::getCancelledPaymentStatus()]);

        $invoice->refresh();

        return $invoice;
    }

    public function viewInvoicesAdminCP(User $context, array $attributes = []): Paginator
    {
        policy_authorize(InvoicePolicy::class, 'viewAdminCP', $context);

        $startDate = Arr::get($attributes, 'start_date');
        $endDate   = Arr::get($attributes, 'end_date');
        $fullname  = Arr::get($attributes, 'full_name');
        $status    = Arr::get($attributes, 'payment_status');
        $limit     = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = Invoice::query();

        if ($startDate) {
            $query->where('advertise_invoices.paid_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('advertise_invoices.paid_at', '<=', $endDate);
        }

        if ($fullname) {
            $query->join('user_entities', function (JoinClause $joinClause) {
                $joinClause->on('user_entities.id', '=', 'advertise_invoices.user_id');
            })
                ->where('user_entities.name', $this->likeOperator(), '%' . $fullname . '%');
        }

        if ($status) {
            $query->where('advertise_invoices.payment_status', '=', $status);
        }

        return $query->orderByDesc('advertise_invoices.created_at')
            ->with(['userEntity'])
            ->paginate($limit, ['advertise_invoices.*']);
    }

    protected function createTransaction(array $attributes): InvoiceTransaction
    {
        $transaction = new InvoiceTransaction($attributes);

        $transaction->save();

        return $transaction;
    }

    public function deleteInvoice(User $context, int $id): bool
    {
        $invoice = $this->find($id);

        policy_authorize(InvoicePolicy::class, 'delete', $context);

        $invoice->delete();

        return true;
    }

    protected function toOwnerSuccessNotification(User $user, InvoiceTransaction $transaction)
    {
        $params = [$user, new OwnerPaymentSuccessNotification($transaction)];

        Notification::send(...$params);
    }

    public function viewTransactionsInAdminCP(User $context, int $invoiceId): Collection
    {
        $invoiceTransactions      = new InvoiceTransaction();
        $invoiceTransactionsTable = $invoiceTransactions->getTable();
        $invoiceTable             = $this->getModel()->getTable();

        return $invoiceTransactions->query()
            ->join("$invoiceTable", function (JoinClause $joinClause) use ($invoiceTransactionsTable, $invoiceTable) {
                $joinClause->on("$invoiceTable.id", '=', "$invoiceTransactionsTable.invoice_id");
            })
            ->where("$invoiceTransactionsTable.invoice_id", $invoiceId)
            ->orderByDesc("$invoiceTransactionsTable.id")
            ->get(["$invoiceTransactionsTable.*"]);
    }

    /**
     * @throws AuthorizationException
     */
    public function viewInvoice(User $context, int $id): Invoice
    {
        $invoice = $this->find($id);

        policy_authorize(InvoicePolicy::class, 'view', $context, $invoice);

        return $invoice;
    }

    protected function getExtraPaymentWithInvoice(array $attributes): array
    {
        return Arr::only($attributes, ['currency_payment', 'price_payment', 'payment_gateway_balance_currency']);
    }
}
