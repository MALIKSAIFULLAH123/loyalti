<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Models\PrivacyStream;

class GetPrivacyIdListener
{
    public function handle($itemId, $itemType): array
    {
        return PrivacyStream::query()
            ->where('item_id', '=', $itemId)
            ->where('item_type', '=', $itemType)
            ->get('privacy_id')
            ->pluck('privacy_id')
            ->toArray();
    }
}
