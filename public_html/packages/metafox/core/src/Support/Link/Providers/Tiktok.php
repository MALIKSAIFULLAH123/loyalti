<?php

namespace MetaFox\Core\Support\Link\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

/**
 * @SuppressWarnings(PHPMD)
 */
class Tiktok extends AbstractLinkProvider
{
    public const OEMBED_TARGET_URL = 'https://www.tiktok.com/oembed';

    public const EMBED_RESOURCE_URL = 'https://www.tiktok.com/embed';

    public const URL_PATTERN = '/(?:https?:\/\/www\.)?tiktok\.com\/(?:\S+)/';

    public function verifyUrl(string $url, &$matches = []): bool
    {
        return preg_match(self::URL_PATTERN, $url, $matches) === 1;
    }

    public function parseUrl(string $url): ?array
    {
        if (!$this->verifyUrl($url, $matches)) {
            return null;
        }

        $bodyData = null;
        try {
            $response = Http::get(self::OEMBED_TARGET_URL, ['url' => $url]);
            if ($response->successful()) {
                $bodyData = $response->json();
            }
        } catch (\Exception $th) {
            return null;
        }

        if (!is_array($bodyData)) {
            return null;
        }

        $description    = Arr::get($bodyData, 'title', null);
        $embed          = Arr::get($bodyData, 'html', null);
        $embedWidth     = Arr::get($bodyData, 'width', 0);
        $embedHeight    = Arr::get($bodyData, 'height', 0);

        if (!empty($embed)) {
            $regex = '/(.|\n)*<blockquote[^>]*><p>((.|\n)*)?<\/p>(.|\n)*/';

            $regex2 = '/(.|\n)*<blockquote[^>]*>((.|\n)*)?<\/blockquote>(.|\n)*/';

            $tryDesc = null;
            if (preg_match($regex, $embed)) {
                $tryDesc = preg_replace($regex, '$2', $embed);
            } elseif (preg_match($regex2, $embed)) {
                $tryDesc = preg_replace($regex2, '$2', $embed);
            }

            if (is_string($tryDesc)) {
                $description = strip_tags($tryDesc);
            }
        }

        $title = $bodyData['author_name'] ?? Arr::get($bodyData, 'provider_name', '');
        $host  = $bodyData['provider_url'] ?? 'www.titok.com';
        $image = Arr::get($bodyData, 'thumbnail_url');

        $data = [
            'embed'        => $embed,
            'embed_width'  => $embedWidth,
            'embed_height' => $embedHeight,
            'is_video'     => !empty($embed) ? true : false,
            'title'        => $title,
            'description'  => $description,
            'host'         => $host,
            'image'        => $image,
        ];

        $authorUrl   = Arr::get($bodyData, 'author_url', '');
        $productId   = Arr::get($bodyData, 'embed_product_id');
        $productType = Arr::get($bodyData, 'embed_type');

        if ($authorUrl && $productId && $productType) {
            Arr::set($data, 'url', sprintf('%s/%s/%s', $authorUrl, $productType, $productId));
        }

        return $data;
    }
}
