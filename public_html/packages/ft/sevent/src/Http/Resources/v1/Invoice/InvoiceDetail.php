<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Invoice;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;
use Foxexpert\Sevent\Http\Resources\v1\InvoiceTransaction\TransactionItemCollection;
use Foxexpert\Sevent\Models\Invoice;
use Foxexpert\Sevent\Models\Ticket;
use Foxexpert\Sevent\Models\Invoice as Model;
use Foxexpert\Sevent\Repositories\InvoiceRepositoryInterface;
use Foxexpert\Sevent\Support\Browse\Traits\Invoice\ExtraTrait;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class InvoiceDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class InvoiceDetail extends JsonResource
{
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $price = app('currency')->getPriceFormatByCurrencyId($this->resource->currency, (float)$this->resource->price);

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->getModuleName(),
            'resource_name'     => $this->resource->entityType(),
            'buyer'             => ResourceGate::asEmbed($this->resource->user),
            'seller'            => ResourceGate::asEmbed($this->resource->sevent?->user),
            'sevent'           => $this->getSevent(),
            'status'            => $this->resource->status,
            'status_label'      => $this->resource->status_label,
            'price'             => $price,
            'ticket'          => $this->getTicket(),
            'transactions'      => $this->getTransactions(),
            'table_fields'      => resolve(InvoiceRepositoryInterface::class)->getTransactionTableFields(),
            'creation_date'     => $this->getCreationDate(),
            'modification_date' => $this->getModificationDate(),
            'payment_date'      => $this->getPaymentDate(),
            'extra'             => $this->getExtra(),
        ];
    }

    protected function getSevent(): ?JsonResource
    {
        $sevent = ResourceGate::asDetail($this->resource->sevent, null);
        
        return $sevent;
    }
    protected function getTicket(): ?JsonResource
    {
        $ticket = ResourceGate::asItem(Ticket::find($this->resource->ticket_id), null);
        
        return $ticket;
    }

    protected function getTransactions(): ?ResourceCollection
    {
        if (!$this->resource->transactions()->count()) {
            return null;
        }

        return new TransactionItemCollection($this->resource->transactions);
    }

    protected function getCreationDate(): string
    {
        return Carbon::parse($this->resource->created_at)->format('c');
    }

    protected function getModificationDate(): string
    {
        return Carbon::parse($this->resource->updated_at)->format('c');
    }

    protected function getPaymentDate(): ?string
    {
        if (null === $this->resource->paid_at) {
            return null;
        }

        return Carbon::parse($this->resource->paid_at)->format('c');
    }

    protected function getModuleName(): string
    {
        return 'sevent';
    }
}
