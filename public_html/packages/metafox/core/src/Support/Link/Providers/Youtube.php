<?php

namespace MetaFox\Core\Support\Link\Providers;

use DateInterval;
use DateTime;
use Illuminate\Support\Facades\Log;

/**
 * @SuppressWarnings(PHPMD)
 */
class Youtube extends AbstractLinkProvider
{
    private string $apiKey;

    public function setOptions(array $options): void
    {
        $this->apiKey = $options['api_key'] ?? '';
    }

    public function verifyUrl(string $url, &$matches = []): bool
    {
        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)|shorts?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

        return preg_match($pattern, $url, $matches) === 1;
    }

    public function parseUrl(string $url): ?array
    {
        if (!$this->verifyUrl($url, $matches)) {
            return null;
        }

        $json = mf_get_contents('https://www.googleapis.com/youtube/v3/videos?id=' . $matches[1] . '&key=' . $this->apiKey . '&part=snippet,contentDetails', [
            CURLOPT_REFERER => url()->current(),
        ]);
        if (!is_string($json)) {
            return null;
        }

        $oYTData = json_decode($json);
        if (isset($oYTData->error) && $oYTData->error->code === 403) {
            Log::error($oYTData->error->message);

            return null;
        }

        //Not a valid youtube url
        if (!isset($oYTData->items[0])) {
            return null;
        }

        $start = new DateTime('@0'); // Unix epoch

        $startTimeDuration = 0;
        try {
            $startTimeDuration = $oYTData->items[0]->contentDetails->duration;
        } catch (\Exception $e) {
            // silent;
        }

        $start->add(new DateInterval($startTimeDuration));
        $duration  = (int) $start->format('H') * 60 * 60 + (int) $start->format('i') * 60 + (int) $start->format('s');
        $thumbnail = $oYTData->items[0]->snippet->thumbnails ?? null;
        $id        = $oYTData->items[0]->id;
        $image     = null;

        if (!empty($thumbnail)) {
            foreach (['maxres', 'standard', 'high', 'medium', 'default'] as $size) {
                if (isset($thumbnail->$size)) {
                    $image = $thumbnail->$size->url;
                    break;
                }
            }
        }

        return [
            'title'       => $oYTData->items[0]->snippet->title,
            'image'       => $image,
            'description' => $oYTData->items[0]->snippet->description,
            'is_video'    => (bool) $id,
            'duration'    => sprintf('%s', $duration),
            'embed'       => '<iframe src="//www.youtube.com/embed/' . $oYTData->items[0]->id . '?wmode=transparent" width="480" height="295" allowfullscreen></iframe>',
        ];
    }
}
