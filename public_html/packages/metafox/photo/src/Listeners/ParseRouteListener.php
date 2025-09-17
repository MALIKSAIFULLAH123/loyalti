<?php
namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;

class ParseRouteListener
{
    public function handle(string $url, ?string $route = null, ?array $queryParams = null): ?array
    {
        if (!MetaFox::isMobile() || version_compare(MetaFox::getApiVersion(), 'v1.18', '>=')) {
            return null;
        }

        if (null === $route || null === $queryParams) {
            [$route, $queryParams] = $this->parseUrl($url);
        }

        $route = trim($route, '/');

        if (!preg_match('/^media\/(\d+)\/(.*?)\/(\d+)\/?(.*?)$/', $route, $matches)) {
            return null;
        }

        /**
         * @var PhotoGroupItem|null $mediaItem
         */
        $mediaItem = PhotoGroupItem::query()
            ->where([
                'item_id' => $matches[3],
                'item_type' => $matches[2],
                'group_id'  => $matches[1],
            ])
            ->first();

        if (!$mediaItem?->item instanceof Content) {
            return null;
        }

        return [
            'path' => $mediaItem->item->toRouter(),
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
}
