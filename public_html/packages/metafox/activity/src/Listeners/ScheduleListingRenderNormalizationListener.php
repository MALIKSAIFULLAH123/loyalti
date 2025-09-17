<?php
namespace MetaFox\Activity\Listeners;

use Illuminate\Support\Arr;

class ScheduleListingRenderNormalizationListener
{
    public function handle(?array $data): ?array
    {
        if (null === $data) {
            return null;
        }

        if (Arr::get($data, 'post_type') !== 'activity_post') {
            return null;
        }

        $closeMap = (bool) Arr::get($data, 'close_map_on_feed', false);

        if (is_numeric(Arr::get($data, 'status_background_id'))) {
            $closeMap = true;
        }

        Arr::set($data, 'show_map_on_feed', false === $closeMap);

        return $data;
    }
}
