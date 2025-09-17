<?php

namespace MetaFox\Event\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use MetaFox\Event\Contracts\EventContract;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserPrivacy;

class Event implements EventContract
{
    public function getPrivacyList(): array
    {
        return [
            [
                'privacy_type'    => Model::EVENT_OWNER,
                'privacy'         => MetaFoxPrivacy::ONLY_ME,
                'privacy_icon'    => 'ico-calendar-check',
                'privacy_tooltip' => [
                    'var_name' => 'event::phrase.privacy_tooltip',
                ],
            ],
            [
                'privacy_type'    => Model::EVENT_HOSTS,
                'privacy'         => MetaFoxPrivacy::CUSTOM,
                'privacy_icon'    => 'ico-calendar-check',
                'privacy_tooltip' => [
                    'var_name' => 'event::phrase.privacy_tooltip',
                ],
            ],
            [
                'privacy_type'    => Model::EVENT_MEMBERS,
                'privacy'         => MetaFoxPrivacy::FRIENDS,
                'privacy_icon'    => 'ico-calendar-check',
                'privacy_tooltip' => [
                    'var_name' => 'event::phrase.privacy_tooltip',
                ],
            ],
        ];
    }

    public function checkFeedReactingPermission(User $user, User $owner): ?bool
    {
        if (!$owner instanceof Model) {
            return null;
        }

        return UserPrivacy::hasAccess($user, $owner, 'feed.view_wall');
    }

    public function checkPermissionMassEmail(User $user, int $eventId): bool
    {
        $now             = Carbon::now();
        $latestMassEmail = resolve(EventRepositoryInterface::class)->getLatestMassEmailByUser($user, $eventId);

        if ($latestMassEmail == null) {
            return false;
        }

        return $latestMassEmail > $now;
    }

    public function createLocationWithName(string $locationName): ?array
    {
        $apiKey = Settings::get('core.google.google_map_api_key');

        if (empty($apiKey)) {
            return null;
        }

        $address = $location        = null;

        $locationNameUrl = rawurlencode($locationName);

        try {
            $url      = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=$locationNameUrl&key=$apiKey";

            $response = Http::get($url);

            $results  = $response->object()->results ?? null;

            if ($results) {
                $location = (array) $results[0]->geometry->location;

                $address = $results[0]->formatted_address;
            }
        } catch (\Exception $e) {}

        if (null == $location) {
            return null;
        }

        return [
            'location_address'   => $address,
            'location_name'      => $locationName,
            'location_latitude'  => $location['lat'],
            'location_longitude' => $location['lng'],
        ];
    }

    public function getStatusTexts(Model $event): array
    {
        if ($event->isEnded()) {
            return [
                'label' => __p('event::phrase.ended'),
                'color' => null,
            ];
        }

        if ($event->isUpcoming()) {
            return [
                'label' => __p('core::web.upcoming'),
                'color' => null,
            ];
        }

        return [
            'label' => __p('core::web.ongoing'),
            'color' => null,
        ];
    }
}
