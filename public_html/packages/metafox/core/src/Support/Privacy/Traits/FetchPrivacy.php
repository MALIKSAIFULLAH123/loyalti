<?php

namespace MetaFox\Core\Support\Privacy\Traits;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Core\Models\Privacy;
use MetaFox\Core\Models\PrivacyStream;
use MetaFox\Platform\Contracts\HasResourceStream;

trait FetchPrivacy
{
    /**
     * @param int    $itemId
     * @param string $itemType
     *
     * @return Collection
     */
    public function getPrivacy(int $itemId, string $itemType): Collection
    {
        return Privacy::query()->where([
            'item_id'   => $itemId,
            'item_type' => $itemType,
        ])->get();
    }

    /**
     * @param int    $itemId
     * @param string $itemType
     *
     * @return int[]
     */
    public function getPrivacyIdsFromStream(int $itemId, string $itemType): array
    {
        return PrivacyStream::query()->where([
            'item_id'   => $itemId,
            'item_type' => $itemType,
        ])->pluck('privacy_id')->toArray();
    }

    /**
     * @param HasResourceStream $resourceStream
     *
     * @return int[]
     */
    public function getPrivacyIdsFromResourceStream(HasResourceStream $resourceStream): array
    {
        return $resourceStream->privacyStreams()->pluck('privacy_id')->toArray();
    }
}
