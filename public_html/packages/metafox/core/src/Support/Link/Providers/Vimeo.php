<?php

namespace MetaFox\Core\Support\Link\Providers;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Vimeo\Vimeo as VimeoClient;

/**
 * @SuppressWarnings(PHPMD)
 */
class Vimeo extends AbstractLinkProvider
{
    private string $clientId;
    private string $clientSecret;
    private string $accessToken;

    protected string $oEmbedUrl = 'https://vimeo.com/api/oembed.json';

    public function setOptions(array $options): void
    {
        $this->clientId     = $options['client_id'] ?? '';
        $this->clientSecret = $options['client_secret'] ?? '';
        $this->accessToken  = $options['access_token'] ?? '';
    }

    public function verifyUrl(string $url, &$matches = []): bool
    {
        $pattern = '~https?:\/\/(?:www\.)?vimeo\.com\/([0-9a-z_-]+)(?:[0-9a-z\/]*)~imu';

        return preg_match($pattern, $url, $matches) === 1;
    }

    public function parseUrl(string $url): ?array
    {
        if (!$this->verifyUrl($url, $matches)) {
            return null;
        }

        $iVideoId = $matches[1];

        try {
            $client   = new VimeoClient($this->clientId, $this->clientSecret, $this->accessToken);
            $response = $client->request('/videos/' . $iVideoId, []);

            if (!empty($response['status']) && $response['status'] == 200) {
                $body        = $response['body'];
                $description = null;

                if (Arr::has($body, 'description') && is_string($body['description'])) {
                    $description = trim($body['description']);
                    $description = html_entity_decode($description);
                }

                return [
                    'title'       => $body['name'],
                    'is_video'    => true,
                    'image'       => isset($body['pictures']['sizes']) ? end($body['pictures']['sizes'])['link'] : '',
                    'description' => $description,
                    'embed'       => $body['embed']['html'] ?? '',
                    'duration'    => $body['duration'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            // Silent
        }

        $embedContent = $this->getEmbedContent($url);

        if (is_array($embedContent) && !empty($embedContent)) {
            return $embedContent;
        }

        return null;
    }

    /**
     * Try to parse vimeo.
     *
     * @param  string                    $url
     * @return array<string, mixed>|null
     */
    protected function getEmbedContent(string $url): ?array
    {
        $headers = [
            'Accept-Language: ' . app()->getLocale(),
        ];

        $data        = ['is_video' => true];

        try {
            $response = Http::timeout(15)
            ->withHeaders($headers)
            ->get($this->oEmbedUrl, ['url' => $url]);

            if (!$response->ok()) {
                return null;
            }

            $embedData = $response->json();
            if (!is_array($embedData) || empty($embedData)) {
                return null;
            }

            $data['title']       = Arr::get($embedData, 'title');
            $data['description'] = Arr::get($embedData, 'description');
            $data['image']       = Arr::get($embedData, 'thumbnail_url');
            $data['embed']       = Arr::get($embedData, 'html') ?: '';

            return $data;
        } catch (\Throwable $error) {
            Log::error($error->getMessage());
            Log::error($error->getTraceAsString());
        }

        return null;
    }
}
