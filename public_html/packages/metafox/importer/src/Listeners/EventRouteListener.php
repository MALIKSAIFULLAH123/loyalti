<?php

namespace MetaFox\Importer\Listeners;

use MetaFox\Importer\Repositories\EntryRepositoryInterface;

class EventRouteListener
{
    public const REGEX_URL_PATTERNS = [
        '/(pages|groups)\/(\d+)\/wall\/comment-id_(\d+)/',
        '/\?(status-id|link-id)=(\d+)/',
    ];

    /**
     * @param string $url
     *
     * @return array<string,mixed>|void
     */
    public function handle(string $url): ?array
    {
        foreach (self::REGEX_URL_PATTERNS as $pattern) {
            if (!preg_match($pattern, $url, $matches)) {
                continue;
            }

            $path = $this->processMatch($matches);
            if ($path) {
                return [
                    'path' => $path,
                ];
            }
        }

        return null;
    }

    private function processMatch(array $matches): ?string
    {
        $entryType = match ($matches[1]) {
            'pages'   => 'page#',
            'groups'  => 'group#',
            'link-id' => 'link#',
            default   => 'activity_post#user_status_',
        };

        $pageEntry = $this->entryRepository()->getEntry($entryType . $matches[2], 'phpfox');

        $entry = $pageEntry && isset($matches[3])
            ? $this->entryRepository()->getEntry("activity_post#{$matches[1]}_comment_$matches[3]", 'phpfox')
            : $this->entryRepository()->getEntry($entryType . $matches[2], 'phpfox');

        if (!$entry) {
            return null;
        }

        $feed = app('events')->dispatch(
            'activity.get_feed_by_item_id',
            [user(), $entry->resource_id, $entry->resource_type, $entry->resource_type],
            true
        );

        return $feed ? "/feed/$feed->id" : "/$pageEntry->resource_type/$pageEntry->resource_id/feed/$feed->id";
    }

    protected function entryRepository(): EntryRepositoryInterface
    {
        return resolve(EntryRepositoryInterface::class);
    }
}
