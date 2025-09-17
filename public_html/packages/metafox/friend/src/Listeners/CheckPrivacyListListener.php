<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Support\Facades\Validator;
use MetaFox\Friend\Rules\FriendListRule;

/**
 * Class CheckPrivacyListListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CheckPrivacyListListener
{
    /**
     * @param  int[] $privacyList
     * @param  int   $ownerId
     * @return bool
     */
    public function handle(array $privacyList, int $ownerId)
    {
        $data = Validator::make(['privacy_list' => $privacyList], [
            'privacy_list' => new FriendListRule($ownerId),
        ]);

        if ($data->invalid()) {
            return false;
        }

        return true;
    }
}
