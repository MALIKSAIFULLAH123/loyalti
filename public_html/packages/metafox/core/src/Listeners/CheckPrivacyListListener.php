<?php

namespace MetaFox\Core\Listeners;

use Illuminate\Support\Str;
use MetaFox\Core\Models\Privacy;

/**
 * Class CheckPrivacyListListener
 * Listener from privacy.checkPrivacyList event.
 */
class CheckPrivacyListListener
{
    /**
     * Call to module to validate. If validated, we will get privacy_id list.
     *
     * @param int[]  $privacyList
     * @param int    $ownerId
     * @param string $privacyType - Current supports friend_list.
     *
     * @return bool|int[]
     */
    public function handle(array $privacyList, int $ownerId, string $privacyType = 'friend_list')
    {
        if (empty($privacyList)) {
            return false;
        }

        $eventName = Str::camel($privacyType);

        /** @var bool $check */
        $check = app('events')->dispatch("$eventName.check_privacy_list", [$privacyList, $ownerId], true);

        if (!$check) {
            return false;
        }

        /** @var int[] $privacyList */
        $privacyList = Privacy::query()
            ->whereIn('item_id', $privacyList)
            ->where('item_type', '=', $privacyType)
            ->get('privacy_id')
            ->pluck('privacy_id')
            ->toArray();

        return !empty($privacyList) ? $privacyList : false;
    }
}
