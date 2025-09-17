<?php

namespace MetaFox\Core\Support\Link;

use Exception;
use Illuminate\Support\Arr;
use MetaFox\Core\Contracts\FetchLinkInterface;
use MetaFox\Core\Contracts\LinkSupportContract;
use MetaFox\Core\Models\Link;

/**
 * @SuppressWarnings(PHPMD)
 */
class FetchLink implements FetchLinkInterface
{
    protected LinkSupportContract $provider;

    public function __construct(LinkSupportContract $provider)
    {
        $this->provider = $provider;
    }

    public function parse(string $url): ?array
    {
        if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://') {
            $url = 'http://' . $url;
        }

        $urlParts = $this->getParseUrl($url);

        if (null === $urlParts) {
            return null;
        }

        $data = $this->provider->parseUrl($url);

        if (empty($data)) {
            return null;
        }

        $title       = isset($data['title']) ? html_entity_decode($data['title'], ENT_QUOTES) : null;
        $description = isset($data['description']) ? html_entity_decode($data['description'], ENT_QUOTES) : null;
        $embed       = $data['embed'] ?? null;
        $image       = $data['image'] ?? null;

        if ($image && strpos($image, 'http') === false && !preg_match('/^\/\//', $image) && isset($urlParts['scheme'])) {
            $image = $urlParts['scheme'] . '://' . $urlParts['host'] . $image;
        }

        if (empty($title) && empty($description) && empty($image) && empty($embed)) {
            return null;
        }

        $isImage = false;

        try {
            $isImage = isImageUrl($url);
        } catch (Exception $e) {
        }

        return [
            'resource_name' => Link::ENTITY_TYPE,
            'title'         => $title ?? Arr::get($urlParts, 'host'),
            'description'   => $description,
            'image'         => $image,
            'is_image'      => $isImage,
            'is_video'      => $data['is_video'] ?? false,
            'link'          => Arr::get($data, 'url') ?? $url,
            'embed_code'    => $embed,
            'duration'      => $data['duration'] ?? null,
            'host'          => $urlParts['host'],
            'width'         => $data['embed_width'] ?? null,
            'height'        => $data['embed_height'] ?? null,
        ];
    }

    public function getDefault(string $url, ?array $urlParts = null): ?array
    {
        $urlParts = $urlParts ?: $this->getParseUrl($url);

        if (null === $urlParts) {
            return null;
        }

        $host = Arr::get($urlParts, 'host');

        return [
            'resource_name' => Link::ENTITY_TYPE,
            'title'         => $host,
            'description'   => null,
            'image'         => null,
            'is_image'      => false,
            'is_video'      => false,
            'link'          => $url,
            'embed_code'    => null,
            'duration'      => null,
            'host'          => $host,
            'width'         => null,
            'height'        => null,
        ];
    }

    protected function getParseUrl(string $url): ?array
    {
        $urlParts = parse_url($url);

        if (!is_array($urlParts) || !Arr::has($urlParts, 'host')) {
            return null;
        }

        return $urlParts;
    }
}
