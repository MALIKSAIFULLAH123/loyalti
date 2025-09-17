<?php

namespace MetaFox\Activity\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Facades\Settings;

class PinPostManager extends StreamManager
{
    public function getProfileLimit(): int
    {
        return Settings::get('activity.feed.total_pin_in_profile', 3);
    }

    public function getHomepageLimit(): int
    {
        return Settings::get('activity.feed.total_pin_in_homepage', 3);
    }

    protected function queryProfileFeed(): Builder
    {
        $query = parent::queryProfileFeed();

        $query->leftJoin('activity_pins as pin', function (JoinClause $join) {
            $join->on('pin.feed_id', '=', 'stream.feed_id');
            $join->where('pin.user_id', '=', $this->getOwnerId());
        })
            ->whereNotNull('pin.id')
            ->limit($this->getProfileLimit());

        $query->orderBy('pin.updated_at', 'DESC');

        return $query;
    }

    public function fetchStream(?int $lastFeedId = null, ?string $timeFrom = null, ?string $timeTo = null)
    {
        $query = $this->buildQuery($lastFeedId, $timeFrom, $timeTo);

        if ($query == null) {
            return collect([]);
        }

        $limit = $this->isViewOnProfile() ? $this->getProfileLimit() : $this->getHomepageLimit();

        $query->limit($limit);

        return $query->get();
    }
}
