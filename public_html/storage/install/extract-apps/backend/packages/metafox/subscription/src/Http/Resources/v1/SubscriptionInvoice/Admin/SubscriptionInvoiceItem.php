<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use MetaFox\Subscription\Support\Browse\Traits\SubscriptionInvoice\Admin\ExtraTrait;
use MetaFox\Subscription\Support\Helper;
use MetaFox\Platform\Contracts\User;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class SubscriptionInvoiceItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class SubscriptionInvoiceItem extends JsonResource
{
    use ExtraTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $user = $this->resource->user;

        $package = $this->resource->package;

        $activatedAt = $expiredAt = null;

        if (is_string($this->resource->activated_at)) {
            $activatedAt = Carbon::parse($this->resource->activated_at)->format('c');
        }

        if (is_string($this->resource->expired_at)) {
            $expiredAt = Carbon::parse($this->resource->expired_at)->format('c');
        }

        return [
            'id'             => $this->resource->entityId(),
            'module_name'    => Helper::MODULE_NAME,
            'resource_name'  => $this->resource->entityType() . '_admincp',
            'user'           => $user instanceof User ? ResourceGate::asEmbed($user) : null,
            'payment_status' => Helper::getPaymentStatusLabel($this->resource->payment_status),
            'package_title'  => $package?->toTitle(),
            'activated_at'   => $activatedAt,
            'expired_at'     => $expiredAt,
            'cancelled_at'   => $this->resource->userCanceledReason?->created_at,
            'extra'          => $this->getExtra(),
            'detail'         => $this->resource->toAdmincpUrl(),
        ];
    }
}
