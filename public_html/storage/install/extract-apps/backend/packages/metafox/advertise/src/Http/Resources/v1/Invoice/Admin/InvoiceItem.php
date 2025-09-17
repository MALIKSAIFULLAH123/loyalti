<?php

namespace MetaFox\Advertise\Http\Resources\v1\Invoice\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Invoice as Model;
use MetaFox\Advertise\Support\Support;
use MetaFox\Advertise\Traits\Invoice\ExtraTrait;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityItem;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class InvoiceItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class InvoiceItem extends JsonResource
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
            'id'             => $this->resource->entityId(),
            'module_name'    => 'advertise',
            'resource_name'  => $this->resource->entityType(),
            'item'           => $this->getItem(),
            'user'           => $this->getUser(),
            'transaction_id' => $this->resource->completedTransaction?->transaction_id,
            'paid_at'        => $this->toDate($this->resource->paid_at),
            'payment_status' => $this->resource->payment_status_label,
            'price'          => app('currency')->getPriceFormatByCurrencyId($this->resource->currency_id, $this->resource->price),
            'extra'          => $this->getExtra(),
            'item_title'     => $this->getItemTitle(),
            'sx'             => $this->getSx(),
            'detail'         => $this->resource->toAdmincpUrl(),
        ];
    }

    public function getSx(): array
    {
        $paymentInformation = $this->resource->payment_status_information;

        return [
            'payment_status' => [
                'color' => Arr::get($paymentInformation, 'color', Support::UNPAID_COLOR),
            ],
        ];
    }

    protected function getItemTitle(): ?string
    {
        if (null === $this->resource->item) {
            return $this->resource->item_deleted_title;
        }

        if ($this->resource->item instanceof HasTitle) {
            return $this->resource->item->toTitle();
        }

        return null;
    }

    protected function getUser(): ?JsonResource
    {
        if (null === $this->resource->userEntity) {
            return null;
        }

        return new UserEntityItem($this->resource->userEntity);
    }

    protected function getItem(): ?JsonResource
    {
        if (null === $this->resource->item) {
            return null;
        }

        return ResourceGate::asEmbed($this->resource->item, null);
    }

    protected function toDate(?string $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return Carbon::parse($date)->format('c');
    }
}
