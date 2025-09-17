<?php

namespace MetaFox\Subscription\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Platform\MetaFox;
use MetaFox\Subscription\Models\SubscriptionInvoice;

class SubscriptionRouteListener
{
    public function handle(string $url): ?array
    {
        if (!Str::startsWith($url, 'subscription/')) {
            return null;
        }

        if (MetaFox::isMobile() && trim($url) === 'subscription/package') {
            return [
                'path' => '/subscription/subscription_package/viewAll',
            ];
        }

        $segments = explode('/', $url);

        $id = Arr::last($segments);

        if (!is_numeric($id)) {
            return null;
        }

        $invoice = SubscriptionInvoice::query()
            ->find($id);

        if (null === $invoice) {
            return null;
        }

        return [
            'path' => '/' . implode('/', ['subscription-invoice', $id]),
        ];
    }
}
