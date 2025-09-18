<?php

namespace MetaFox\Marketplace\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Marketplace\Models\Invoice;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Platform\MetaFoxConstant;

class ListingRouteListener
{
    public function handle(string $url, ?string $route = null, ?array $queryParams = null): ?array
    {
        if (null === $route || null === $queryParams) {
            [$route, $queryParams] = $this->parseUrl($url);
        }

        $route = trim($route, '/');

        $segments = explode('/', $route);

        $first = array_shift($segments);

        if ($first !== 'marketplace') {
            return null;
        }

        $second = array_shift($segments);

        if ($second === 'invoice') {
            $id = array_shift($segments);

            if (!is_numeric($id) || $id <= 0) {
                return [];
            }

            return $this->handleInvoice($id);
        }

        if (!is_numeric($second)) {
            return null;
        }

        return $this->handleListing($second);
    }

    protected function handleListing(int $id): ?array
    {
        $listing = Listing::query()
            ->find($id);

        if (null === $listing) {
            return null;
        }

        return [
            'path' => '/' . implode('/', ['marketplace', $id]),
        ];
    }

    private function parseUrl(string $url): array
    {
        $parts = parse_url($url);

        $route = Arr::get($parts, 'path', MetaFoxConstant::EMPTY_STRING);

        $queryString = Arr::get($parts, 'query', MetaFoxConstant::EMPTY_STRING);

        $queryParams = [];

        if (is_string($queryString)) {
            parse_str($queryString, $queryParams);
        }

        return [$route, $queryParams];
    }

    protected function handleInvoice(int $id): ?array
    {
        $invoice = Invoice::query()
            ->find($id);

        if (null === $invoice) {
            return null;
        }

        return [
            'path' => '/' . implode('/', ['marketplace', 'marketplace_invoice', $id]),
        ];
    }
}
