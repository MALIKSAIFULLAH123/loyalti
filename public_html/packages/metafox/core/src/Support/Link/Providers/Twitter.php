<?php

namespace MetaFox\Core\Support\Link\Providers;

use DOMDocument;
use DOMElement;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * @SuppressWarnings(PHPMD)
 */
class Twitter extends AbstractLinkProvider
{
    private string $tokenUrl  = 'https://api.twitter.com/oauth2/token';
    private string $tweetsUrl = 'https://api.twitter.com/2/tweets';
    private string $usersUrl  = 'https://api.twitter.com/2/users';

    private string $apiKey;
    private string $secretKey;

    public const STATUS_URL_PATTERN = '%https?://(www\.)?(twitter|x)\.com/(?:\#!/)?(\w+)/status(es)?/(\d+)%';
    public const USER_URL_PATTERN   = '%https?://(www\.)?(twitter|x)\.com/(#!/)?@?([^/]*)%';

    public function setOptions(array $options): void
    {
        $this->apiKey    = $options['api_key'] ?? '';
        $this->secretKey = $options['secret_key'] ?? '';
    }

    public function verifyUrl(string $url, &$matches = []): bool
    {
        $pattern = '%https?://(www\.)?(twitter|x)\.com%';

        return preg_match($pattern, $url, $matches) === 1;
    }

    public function parseUrl(string $url): ?array
    {
        if (!$this->verifyUrl($url, $matches)) {
            return null;
        }

        $token = $this->getAccessToken();
        if (!$token) {
            return $this->crawlContent($url);
        }

        $reqUrl = $id = $userName = '';
        if (preg_match(self::STATUS_URL_PATTERN, $url, $matches)) {
            if (!empty($matches[4])) {
                $id     = $matches[4];
                $reqUrl = $this->tweetsUrl . '/' . $id . '?expansions=attachments.media_keys,author_id&media.fields=url';
            }
        } elseif (preg_match(self::USER_URL_PATTERN, $url, $matches)) {
            if (!empty($matches[3])) {
                $userName = $matches[3];
                $reqUrl   = $this->usersUrl . '/by?usernames=' . $userName . '&user.fields=profile_image_url,description';
            }
        }

        $content = $this->getContent($reqUrl, $token);
        if (empty($content)) {
            return $this->crawlContent($url);
        }

        if (!empty($id)) {
            return $this->processStatus($content);
        }

        if (!empty($userName)) {
            return $this->processUser($content) ?? null;
        }

        return $this->crawlContent($url);
    }

    private function getAccessToken(): ?string
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
                ->asForm()
                ->post($this->tokenUrl, [
                    'grant_type' => 'client_credentials']);

            if (!$response->successful()) {
                return null;
            }

            return $response->json('access_token');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param string $url
     * @param string $token
     *
     * @return ?array<string, mixed>
     */
    private function getContent(string $url, string $token): ?array
    {
        try {
            $response = Http::withToken($token)->get($url);

            if (!$response->successful()) {
                return null;
            }

            return $response->json('data');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param array<mixed> $content
     *
     * @return array<string, mixed>
     */
    private function processStatus(array $content): array
    {
        if (isset($content['includes'])) {
            $media = null;
            $user  = null;
            if (isset($content['includes']['media'])) {
                $media = array_shift($content['includes']['media']);
            }

            if (isset($content['includes']['users'])) {
                $user = array_shift($content['includes']['users']);
            }

            $image = $media ? $media['url'] : '';
            $title = $user ? $user['name'] . ' on Twitter' : '';
        }

        return [
            'title'       => $title ?? null,
            'description' => $content['text'] ?? null,
            'image'       => $image ?? null,
        ];
    }

    /**
     * @param array<mixed> $content
     *
     * @return array<string, mixed>
     */
    private function processUser(array $content): ?array
    {
        $user = array_shift($content);
        if (empty($user)) {
            return null;
        }

        $image = null;
        if (isset($user['profile_image_url'])) {
            $image = str_replace('_normal', '', $user['profile_image_url']);
        }

        return [
            'title'       => $user['name'] ?? '',
            'description' => $user['description'] ?? '',
            'image'       => $image,
        ];
    }
    /**
     * Try to crawl the content using HTTP client.
     *
     * @param  string               $url
     * @return array<string, mixed>
     */
    public function crawlContent(string $url): array
    {
        $response    = Http::withHeaders(['User-Agent' => 'twitterbot'])->get($url);
        $domDocument = new DOMDocument();
        $domDocument->loadHTML($response->body(), LIBXML_NOERROR);

        if ($response->successful()) {
            return $this->parseSuccessfulHtml($domDocument);
        }

        return $this->parseFailedHtml($domDocument);
    }

    /**
     * Get the content from success twitter url.
     *
     * @param  DOMDocument          $html
     * @return array<string, mixed>
     */
    protected function parseSuccessfulHtml(DOMDocument $html): array
    {
        $metas = $html->getElementsByTagName('meta');
        $data  = $this->getDefaultContent();

        foreach ($metas as $meta) {
            if (!$meta instanceof DOMElement) {
                continue;
            }

            $property = $meta->getAttribute('property');

            match ($property) {
                'og:title'       => $data['title']       = $meta->getAttribute('content'),
                'og:description' => $data['description'] = $meta->getAttribute('content'),
                'og:image'       => $data['image']       = $meta->getAttribute('content'),
                default          => null,
            };
        }

        return $data;
    }

    /**
     * Get the content from error twitter url.
     *
     * @param  DOMDocument          $html
     * @return array<string, mixed>
     */
    protected function parseFailedHtml(DOMDocument $html): array
    {
        $data = $this->getDefaultContent();

        $title = $html->getElementsByTagName('title')->item(0)?->nodeValue;
        if (!empty($title)) {
            $data['title'] =  $title;
        }

        $description = $html->getElementById('description')?->nodeValue;
        if (!empty($description)) {
            $data['description'] = $description;
        }

        $image = $html->getElementById('image');
        if ('img' === $image?->nodeName) {
            $data['image'] = $image->getAttribute('src');
        }

        return $data;
    }

    /**
     * The default fallback data.
     *
     * @return array<string, mixed>
     */
    protected function getDefaultContent(): array
    {
        return [
            'title'       => 'X (formerly Twitter)',
            'description' => 'From breaking news and entertainment to sports and politics, get the full story with all the live commentary.',
            'image'       => 'https://abs.twimg.com/responsive-web/client-web/icon-ios.77d25eba.png',
        ];
    }
}
