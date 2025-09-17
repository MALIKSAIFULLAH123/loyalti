<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MetaFox\Marketplace\Models\Invoice;
use MetaFox\Marketplace\Models\InvoiceTransaction;
use MetaFox\Marketplace\Repositories\InvoiceAdminRepositoryInterface;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class InvoiceRepository.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class InvoiceAdminRepository extends AbstractRepository implements InvoiceAdminRepositoryInterface
{
    public function model()
    {
        return Invoice::class;
    }

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Paginator
     */
    public function viewInvoices(User $context, array $attributes = []): Paginator
    {
        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->buildQueryViewInvoices($context, $attributes);

        return $query->paginate($limit, ['marketplace_invoices.*']);
    }

    private function buildQueryViewInvoices(User $context, array $attributes): Builder
    {
        $status    = Arr::get($attributes, 'status');
        $listingId = Arr::get($attributes, 'listing_id');
        $dateFrom  = Arr::get($attributes, 'from');
        $dateTo    = Arr::get($attributes, 'to');

        $query = $this->getModel()
            ->newModelQuery()
            ->with(['listing']);

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
        return $this->find($id);
    }

    public function deleteInvoice(User $context, int $id): bool
    {
        $invoice = $this->find($id);

        $invoice->delete();

        return true;
    }

    public function cancelInvoice(User $context, int $id): Invoice
    {
        $invoice = $this->find($id);

        $invoice->update(['status' => ListingFacade::getCanceledPaymentStatus()]);

        $invoice->refresh();

        return $invoice;
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

}
