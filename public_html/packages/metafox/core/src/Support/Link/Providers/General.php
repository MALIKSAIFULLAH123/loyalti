<?php

namespace MetaFox\Core\Support\Link\Providers;

use DOMDocument;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use MediaEmbed\MediaEmbed;
use MediaEmbed\Object\MediaObject;
use MetaFox\Core\Models\Link;

/**
 * @SuppressWarnings(PHPMD)
 */
class General extends AbstractLinkProvider
{
    public function verifyUrl(string $url, &$matches = []): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) != false;
    }

    public function parseUrl(string $url): ?array
    {
        if (!$this->verifyUrl($url)) {
            return null;
        }

        $aRegMatches = [];
        $encoding    = 'utf-8';
        $title       = null;
        $description = null;

        $sContent = $this->getContent($url);

        if (empty($sContent)) {
            return null;
        }

        if (function_exists('imagecreatefromstring')) {
            try {
                $tryImage = imagecreatefromstring($sContent);
                if ($tryImage !== false) {
                    $image = $url;
                }
            } catch (\Exception $e) {
                // Silent.
            }
        }

        // special case check encoding from html
        preg_match('/<html(.*?)>/i', $sContent, $aRegMatches);
        preg_match('/<meta[^<].*charset=["]?([\w-]*)["]?/i', $sContent, $aCharsetMatches);
        if (isset($aCharsetMatches[1])) {
            $encoding = $aCharsetMatches[1];
        } elseif (isset($aRegMatches[1])) {
            preg_match('/lang=["|\'](.*?)["|\']/is', $aRegMatches[1], $aLanguages);
            if (isset($aLanguages[1]) && in_array($aLanguages[1], ['uk'])) {
                $encoding = 'Windows-1251';
            }
        }

        // get title
        if (preg_match('/<title>(.*?)<\/title>/is', $sContent, $aMatches)) {
            $title = $aMatches[1];
        } elseif (preg_match('/<title (.*?)>(.*?)<\/title>/is', $sContent, $aMatches) && isset($aMatches[2])) {
            $title = $aMatches[2];
        }

        // get all meta tags
        $aParseBuild = [];
        preg_match_all('/<(meta|link)([^>]+)?/i', $sContent, $aRegMatches);
        if (!empty($aRegMatches[2])) {
            foreach ($aRegMatches[2] as $sLine) {
                $sLine = rtrim($sLine, '/');
                $sLine = trim($sLine);
                preg_match('/(property|name|rel|image_src)=("|\')([^\'"]+)?("|\')/is', $sLine, $aType);
                if (count($aType) && isset($aType[3])) {
                    $sType = $aType[3];
                    preg_match('/(content|type)=(")([^"]+)?(")/i', $sLine, $aValue);
                    if (count($aValue) && isset($aValue[3])) {
                        if ($sType == 'alternate') {
                            $sType = $aValue[3];
                            preg_match('/href=("|\')([^\'"]+)?("|\')/i', $sLine, $aHref);
                            if (isset($aHref[2])) {
                                $aValue[3] = $aHref[2];
                            }
                        }
                        $aParseBuild[$sType] = $aValue[3];
                    }
                }
            }
        }

        // get meta with DOMDocument when $aParseBuild empty
        if (empty($aParseBuild) && !empty($sContent)) {
            $doc = new DOMDocument('1.0', $encoding);
            // now we inject another meta tag
            $contentType = '<meta http-equiv="Content-Type" content="text/html; charset=' . $encoding . '">';
            $sContent    = str_replace('<head>', '<head>' . $contentType, $sContent);

            if (function_exists('mb_convert_encoding')) {
                @$doc->loadHTML(mb_convert_encoding($sContent, 'HTML-ENTITIES', $encoding));
            } else {
                @$doc->loadHTML($sContent);
            }
            $metaList = $doc->getElementsByTagName('meta');
            foreach ($metaList as $iKey => $meta) {
                $type = $meta->getAttribute('property');
                if (empty($type)) {
                    $type = $meta->getAttribute('name');
                }
                $aParseBuild[$type] = $meta->getAttribute('content');
            }
        }

        if (!empty($aParseBuild['og:image']) || !empty($aParseBuild['twitter:image'])) {
            $image = !empty($aParseBuild['og:image']) ? $aParseBuild['og:image'] : $aParseBuild['twitter:image'];
        } else {
            preg_match('/http(?:s?):\/\/(?:www\.|web\.|m\.)?facebook\.com\/([A-z0-9\.]+)\/videos(?:\/[0-9A-z].+)?\/(\d+)(?:.+)?$/', $url, $aFbVideo);
            if (!empty($aFbVideo[2])) {
                $image = 'https://graph.facebook.com/' . $aFbVideo[2] . '/picture';
            }
        }

        if (empty($aParseBuild['og:type']) || !preg_match('/^video(\.)?/', $aParseBuild['og:type'])) {
            // check and get title
            if (!empty($aParseBuild['og:title']) || !empty($aParseBuild['twitter:title'])) {
                $title = !empty($aParseBuild['og:title']) ? $aParseBuild['og:title'] : $aParseBuild['twitter:title'];
            }

            // check and get description
            if (!empty($aParseBuild['description']) || !empty($aParseBuild['og:description']) || !empty($aParseBuild['twitter:description'])) {
                $description = !empty($aParseBuild['description']) ? $aParseBuild['description'] : (!empty($aParseBuild['og:description']) ? $aParseBuild['og:description'] : ($aParseBuild['twitter:description'] ?? ''));
            }
        }
        // check and get embed media
        $embed = $this->getEmbedMedia($url);
        if (empty($embed)) {
            if (isset($aParseBuild['application/json+oembed'])) {
                stream_context_create(
                    [
                        'http' => [
                            'header'     => 'Connection: close',
                            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        ],
                    ]
                );
                $embedContent = mf_get_contents($aParseBuild['application/json+oembed']);
                $embedContent = $embedContent ? preg_replace('/[^(\x20-\x7F)]*/', '', $embedContent) : null;
                $source       = $embedContent ? json_decode($embedContent) : null;
                if ($source && isset($source->html) && is_string(Arr::get($aParseBuild, 'al:android:url'))) {
                    $id    = str_replace('fb://photo/', '', $aParseBuild['al:android:url']);
                    $image = 'https://graph.facebook.com/' . $id . '/picture';
                    $embed = '<div class="fb_video_iframe"><iframe src="https://www.facebook.com/video/embed?video_id=' . $id . '"></iframe></div>';
                }
            }
        }

        // check and get duration
        if (!empty($aParseBuild['duration']) || !empty($aParseBuild['video:duration'])) {
            $duration = !empty($aParseBuild['duration']) ? $aParseBuild['duration'] : $aParseBuild['video:duration'];
        } elseif (preg_match('/(.*?)duration":{"raw":(.*?),/', $sContent, $aMatches)) {
            if (isset($aMatches[2])) {
                $duration = $aMatches[2];
            }
        }

        // check encoding and convert string to requested character encoding
        if ($encoding != 'utf-8') {
            $title       = iconv($encoding, 'utf-8', $title);
            $description = iconv($encoding, 'utf-8', $description);
        }

        // The image url is htmlspecialchars encoded
        $image = isset($image) ? htmlspecialchars_decode($image) : null;

        if (is_string($title)) {
            $title = htmlentities(html_entity_decode($title));
        }

        return [
            'resource_name' => Link::ENTITY_TYPE,
            'title'         => $title,
            'description'   => $description ?? null,
            'image'         => $image,
            'link'          => $url ?? null,
            'embed_code'    => $embed ?? null,
            'duration'      => $duration ?? null,
        ];
    }

    private function getEmbedMedia(string $embedUrl): ?string
    {
        $embed  = null;
        $aParts = parse_url($embedUrl);
        if (empty($aParts)) {
            return null;
        }

        if (preg_match('/dailymotion/', $embedUrl) && substr($embedUrl, 0, 8) == 'https://') {
            $embedUrl = str_replace('https', 'http', $embedUrl);
        }
        if (preg_match('/facebook\.com/', $embedUrl)) {
            $embedUrl = rtrim($embedUrl, '/') . '/';
        }
        $MediaEmbed  = new MediaEmbed();
        $mediaObject = $MediaEmbed->parseUrl($embedUrl);
        if ($mediaObject instanceof MediaObject) {
            if ($embedImage = $mediaObject->image()) {
                $image = $embedImage;
                if (preg_match('#^//#', $image) === 1 && isset($aParts['scheme'])) {
                    $image = preg_replace('#^//#', $aParts['scheme'] . '://', $image);
                }
            }
            $embed = $mediaObject->getEmbedCode();
        }

        return $embed;
    }

    /**
     * @param string $url URL of the server.
     *
     * @return ?string
     */
    private function getContent(string $url): ?string
    {
        $headers = [
            'Accept-Language' => app()->getLocale(),
        ];

        if ($frontendUrl = config('app.url')) {
            $headers['Origin'] = $frontendUrl;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->withUserAgent('facebookexternalhit/1.1')
                ->get($url);

            if ($response->ok()) {
                return $response->body();
            }

            $browserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36';

            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->withUserAgent($browserAgent)
                ->get($url);

            return $response->body();
        } catch (Exception $e) {
            return null;
        }
    }
}
