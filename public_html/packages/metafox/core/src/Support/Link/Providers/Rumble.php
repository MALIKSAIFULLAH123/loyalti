<?php

namespace MetaFox\Core\Support\Link\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMElement;

/**
 * @SuppressWarnings(PHPMD)
 */
class Rumble extends AbstractLinkProvider
{
    public const EMBED_URL_PATTERN = '/<link.*?href="(https\:\/\/rumble\.com\/api\/Media\/oembed\.json[^"]*)".*?type=application\/json\+oembed.*?>/i';

    public const URL_PATTERN = '/https?:\/\/(?:www\.)?rumble\.com\/v[\w.-]+-[\w.-]+\.html$/';

    public function verifyUrl(string $url, &$matches = []): bool
    {
        return preg_match(self::URL_PATTERN, $url, $matches) === 1;
    }

    public function parseUrl(string $url): ?array
    {
        if (!$this->verifyUrl($url, $matches)) {
            return null;
        }

        try {
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();

            if (empty($html)) {
                return null;
            }

            $data = array_merge($this->parseVideoInfo($html), $this->parseVideoEmbed($html));

            if (empty($data)) {
                return null;
            }

            return $data;
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function parseVideoInfo(string $html): array
    {
        $data        = [];
        $domDocument = new DOMDocument();
        @$domDocument->loadHTML($html, LIBXML_NOERROR);

        $metas = $domDocument->getElementsByTagName('meta');

        foreach ($metas as $meta) {
            if (!$meta instanceof DOMElement) {
                continue;
            }

            $this->extractMetaContent($meta, $data);
        }

        return $data;
    }

    private function extractMetaContent(DOMElement $meta, array &$data): void
    {
        $property = $meta->getAttribute('property');
        $content  = $meta->getAttribute('content');

        switch ($property) {
            case 'og:title':
                $data['title'] = $content;
                break;
            case 'og:description':
                $data['description'] = $content;
                break;
            case 'og:image':
                $data['image'] = $content;
                break;
        }
    }

    private function parseVideoEmbed(string $html): ?array
    {
        $embedVideoUrl = $this->extractVideoEmbedUrl($html);

        if (!$embedVideoUrl) {
            return null;
        }

        $jsonData = $this->fetchVideoEmbedData($embedVideoUrl);

        if (empty($jsonData)) {
            return null;
        }

        return $this->formatVideoEmbedData($jsonData);
    }

    private function extractVideoEmbedUrl(string $html): ?string
    {
        if (preg_match(self::EMBED_URL_PATTERN, $html, $matches)) {
            return urldecode($matches[1]);
        }

        return null;
    }

    private function fetchVideoEmbedData(string $embedVideoUrl): ?array
    {
        try {
            $response = Http::timeout(30)->get($embedVideoUrl);
            if (!$response->ok()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function formatVideoEmbedData(array $jsonData): array
    {
        $data = [];

        $data['title']        = Arr::get($jsonData, 'title');
        $data['image']        = Arr::get($jsonData, 'thumbnail_url');
        $data['embed']        = Arr::get($jsonData, 'html') ?: '';
        $data['duration']     = Arr::get($jsonData, 'duration');
        $data['is_video']     = Arr::get($jsonData, 'type') === 'video';
        $data['embed_width']  = Arr::get($jsonData, 'width');
        $data['embed_height'] = Arr::get($jsonData, 'height');

        return $data;
    }
}
