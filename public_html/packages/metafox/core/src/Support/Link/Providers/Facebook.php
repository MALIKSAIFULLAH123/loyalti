<?php

namespace MetaFox\Core\Support\Link\Providers;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * @SuppressWarnings(PHPMD)
 */
class Facebook extends AbstractLinkProvider
{
    private string $appId = '';

    private string $appSecret = '';

    private string $graphDomain = 'https://graph.facebook.com';

    private string $graphVersion = '8.0';

    public const FACEBOOK_GRAPH_POST = 'oembed_post';

    public const FACEBOOK_GRAPH_VIDEO = 'oembed_video';

    /**
     * @property array<string> $patterns
     */
    private array $patterns = [
        '/http(?:s?):\/\/(?:www\.|web\.|m\.)?facebook\.com\/([A-z0-9\.]+)\/videos(?:\/[0-9A-z].+)?\/(\d+)(?:.+)?$/',
        '/http(?:s?):\/\/(?:www\.|web\.|m\.)?facebook\.com\/(reel|share\/(?:r|v))(?:\/[0-9A-z].+)?\/(\w+)(?:.+)?$/',
        '/http(?:s?):\/\/(fb\.watch)\/([A-z0-9_\-]+)/',
    ];

    /**
     * @param array $options
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->appId     = $options['app_id'] ?? '';
        $this->appSecret = $options['app_secret'] ?? '';

        if (isset($options['domain'])) {
            $this->graphDomain = $options['domain'];
        }

        if (isset($options['version'])) {
            $this->graphVersion = $options['version'];
        }

        if (isset($options['test_patterns'])) {
            $this->patterns = $options['test_patterns'];
        }
    }

    public function verifyUrl(string $url, &$matches = []): bool
    {
        $pattern = '/http(?:s?):\/\/(?:www\.|web\.|m\.)?(facebook\.com)|(fb\.watch)/';

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

        //Force open source agent to get more information
        try {
            $response = $this->get($this->getEndpoint($url), ['url' => $url]);
            if (!$response->successful()) {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }

        $aBody       = $response->json();
        $description = null;
        $embed       = null;
        $embedWidth  = 0;
        $embedHeight = 0;

        if (!empty($aBody['html'])) {
            $embed = $aBody['html'];

            if (isset($aBody['width'])) {
                $embedWidth = $aBody['width'];
            }

            if (isset($aBody['height'])) {
                $embedHeight = $aBody['height'];
            }

            $regex = '/(.|\n)*<blockquote[^>]*><p>((.|\n)*)?<\/p>(.|\n)*/';

            $regex2 = '/(.|\n)*<blockquote[^>]*>((.|\n)*)?<\/blockquote>(.|\n)*/';

            $tryDesc = null;
            if (preg_match($regex, $aBody['html'])) {
                $tryDesc = preg_replace($regex, '$2', $aBody['html']);
            } elseif (preg_match($regex2, $aBody['html'])) {
                $tryDesc = preg_replace($regex2, '$2', $aBody['html']);
            }

            if (is_string($tryDesc)) {
                $description = strip_tags($tryDesc);
            }
        }

        $title = $aBody['author_name'] ?? ($aBody['provider_name'] ?: '');
        $host  = $aBody['provider_url'] ?: 'www.facebook.com';

        return [
            'embed'        => $embed,
            'embed_width'  => $embedWidth,
            'embed_height' => $embedHeight,
            'is_video'     => $this->isVideo($url),
            'title'        => $title,
            'description'  => $description,
            'host'         => $host,
        ];
    }

    protected function getToken(): string
    {
        return sprintf('%s|%s', $this->appId, $this->appSecret);
    }

    protected function getEndpoint(string $url): string
    {
        $oembed = $this->getOEmbed($url);

        return sprintf('%s/v%s/%s', $this->graphDomain, $this->graphVersion, $oembed);
    }

    protected function isVideo(string $url): bool
    {
        $replaceVideoUrl = preg_replace($this->patterns, '$2', $url);

        return !empty($replaceVideoUrl) && $replaceVideoUrl != $url;
    }

    protected function get(string $url, array $query = [], array $headers = []): Response
    {
        $headers = array_merge($headers, ['Authorization' => 'Bearer ' . $this->getToken()]);

        return Http::withHeaders($headers)
            // ->withUserAgent('facebookexternalhit/1.1 (+https://www.facebook.com/externalhit_uatext.php)')
            ->get($url, $query);
    }

    protected function getOEmbed(string $url): string
    {
        return $this->isVideo($url) ? self::FACEBOOK_GRAPH_VIDEO : self::FACEBOOK_GRAPH_POST;
    }
}
