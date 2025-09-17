<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use MetaFox\Payment\Contracts\IsBillable;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Support\Payment;
use MetaFox\Platform\Contracts\User;

/**
 * Trait BillableTrait.
 *
 * @mixin Model
 * @mixin IsBillable
 * @property Order $order
 */
trait BillableTrait
{
    public function toOrder(): array
    {
        return [
            'title'        => $this->toTitle(),
            'user_id'      => $this->userId(),
            'user_type'    => $this->userType(),
            'item_id'      => $this->entityId(),
            'item_type'    => $this->entityType(),
            'total'        => $this->getTotal(),
            'currency'     => $this->getCurrency(),
            'payment_type' => $this->getPaymentType(),
        ];
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getPaymentType(): string
    {
        return Payment::PAYMENT_ONETIME;
    }

    public function order(): MorphOne
    {
        return $this->morphOne(Order::class, 'order', 'item_type', 'item_id');
    }

    public function payee(): ?User
    {
        return null;
    }
}
