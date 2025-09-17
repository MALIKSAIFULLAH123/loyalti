<?php

namespace MetaFox\App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class MetaFoxNews
{
    public const CACHE_TIME = 3600;
    public const CACHE_NAME = 'metafox_news';
    /**
     * get News from phpfox.
     * @param  int               $limit
     * @return array<int, mixed>
     * @throws \Exception
     */
    public function getNews(int $limit = 3): array
    {
        return Cache::remember(self::CACHE_NAME, self::CACHE_TIME, function () use ($limit) {
            $data = [];
            try {
                $request = Http::get('https://feeds2.feedburner.com/phpfox', []);
                $body    = $request->body();

                $xml     = new SimpleXMLElement($body);
                $entries = $xml->channel->item;

                foreach ($entries as $item) {
                    if (count($data) >= $limit) {
                        break;
                    }

                    $data[] = $this->processItem($item);
                }
            } catch (\Exception $e) {
                Log::info('Cannot load news!');
                Log::info($e->getMessage());
            }

            return $data;
        });
    }

    /**
     * @param  SimpleXMLElement     $item
     * @return array<string, mixed>
     */
    private function processItem(SimpleXMLElement $item): array
    {
        $content = (string) $item->children('http://purl.org/rss/1.0/modules/content/');
        $creator = htmlentities($item->children('http://purl.org/dc/elements/1.1/'));

        if ($content) {
            preg_match('/<*img[^>]*src*=*["\']([^"\']*)?["\']/i', $content, $aMatch);
            $image = !empty($aMatch[1]) ? $aMatch[1] : '';
        } else {
            $image = $aLinkInfo['default_image'] ?? '';
        }

        return [
            'title'       => htmlentities($item->title),
            'link'        => htmlentities($item->link),
            'description' => htmlentities($item->description),
            'image'       => $image,
            'creator'     => $creator,
            'created_at'  => Carbon::parse($item->pubDate)->toDateTimeString(),
        ];
    }
}
