<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;
use MetaFox\Marketplace\Http\Resources\v1\InvoiceTransaction\TransactionItemCollection;
use MetaFox\Marketplace\Models\Invoice;
use MetaFox\Marketplace\Models\Invoice as Model;
use MetaFox\Marketplace\Repositories\InvoiceRepositoryInterface;
use MetaFox\Marketplace\Support\Browse\Traits\Invoice\ExtraTrait;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;

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
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->getModuleName(),
            'resource_name'     => $this->resource->entityType(),
            'buyer'             => ResourceGate::asEmbed($this->resource->user),
            'seller'            => ResourceGate::asEmbed($this->resource->listing?->user),
            'listing'           => $this->getListing(),
            'status'            => $this->resource->status,
            'status_label'      => $this->resource->status_label,
            'price'             => ListingFacade::getPriceFormat($this->resource->currency, $this->resource->price),
            'transactions'      => $this->getTransactions(),
            'table_fields'      => resolve(InvoiceRepositoryInterface::class)->getTransactionTableFields(),
            'payment_buttons'   => $this->getPaymentButtons(),
            'creation_date'     => $this->getCreationDate(),
            'modification_date' => $this->getModificationDate(),
            'payment_date'      => $this->getPaymentDate(),
            'extra'             => $this->getExtra(),
        ];
    }

    protected function getPaymentButtons(): array
    {
        $buttons = [];

        $policy = PolicyGate::getPolicyFor(Invoice::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        if ($policy->cancel($context, $this->resource)) {
            $buttons[] = [
                'label' => __p('marketplace::phrase.buy_now'),
                'value' => 'marketplace/cancelOnExpiredListing',
                'color' => 'primary',
            ];

            return $buttons;
        }

        if (!$policy->repayment($context, $this->resource)) {
            return [];
        }

        if ($policy->change($context, $this->resource)) {
            $buttons[] = [
                'label' => __p('marketplace::phrase.buy_now'),
                'value' => 'marketplace/changeItem',
                'color' => 'primary',
            ];

            return $buttons;
        }

        $buttons[] = [
            'label' => __p('marketplace::phrase.buy_now'),
            'value' => 'marketplace/getRepaymentForm',
            'color' => 'primary',
        ];

        return $buttons;
    }

    protected function getListing(): ?JsonResource
    {
        $listing = null;

        if (null === $this->resource->listing) {
            $this->resource
                ->load(['listing' => fn ($item) => $item->withTrashed()]);
        }

        if (null !== $this->resource->listing) {
            $listing = ResourceGate::asEmbed($this->resource->listing, null);
        }

        return $listing;
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
        return 'marketplace';
    }
}
