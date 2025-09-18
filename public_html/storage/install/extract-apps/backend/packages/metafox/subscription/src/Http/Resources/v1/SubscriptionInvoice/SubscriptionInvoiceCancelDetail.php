<?php
namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice;

use MetaFox\User\Http\Resources\v1\User\UserMe;

class SubscriptionInvoiceCancelDetail extends SubscriptionInvoiceDetail
{
    public function toArray($request)
    {
        $data = parent::toArray($request);

        if (null === $this->resource->user) {
            return $data;
        }

        return array_merge($data, [
            'user' => new UserMe($this->resource->user),
        ]);
    }
}
