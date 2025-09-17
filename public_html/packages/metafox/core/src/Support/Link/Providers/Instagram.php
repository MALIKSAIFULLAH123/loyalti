<?php

namespace MetaFox\Core\Support\Link\Providers;

/**
 * @SuppressWarnings(PHPMD)
 */
class Instagram extends Facebook
{
    private string $appId     = '';
    private string $appSecret = '';

    public function setOptions(array $options): void
    {
        $this->appId     = $options['app_id'] ?? '';
        $this->appSecret = $options['app_secret'] ?? '';
    }

    public function verifyUrl(string $url, &$matches = []): bool
    {
        $pattern = '/(https?:\/\/www\.)?instagram\.com(\/p\/\w+\/?)/';

        return preg_match($pattern, $url, $matches) === 1;
    }

    public function parseUrl(string $url): ?array
    {
        if (!$this->verifyUrl($url, $matches)) {
            return null;
        }

        if (empty($this->appId) || empty($this->appSecret)) {
            return null;
        }

        try {
            $response = $this->get($this->getEndpoint($url), ['url' => $url]);
            if (!$response->successful()) {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }

        $data = $response->body();

        return [
            'title' => $data['author_name'] ?? ($data['provider_name'] ?: ''),
            'image' => $data['thumbnail_url'] ?? '',
            'embed' => $data['html'],
        ];
    }

    protected function getOEmbed(string $url): string
    {
        return 'instagram_oembed';
    }
}
