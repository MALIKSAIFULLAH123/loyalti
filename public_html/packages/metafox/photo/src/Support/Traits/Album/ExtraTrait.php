<?php

namespace MetaFox\Photo\Support\Traits\Album;

use Illuminate\Support\Arr;
use MetaFox\Photo\Models\Album;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission as ACL;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Contracts\User;

trait ExtraTrait
{
    use HasExtra {
        getExtra as getMainExtra;
    }

    public function getExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(Album::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        $main = $this->getMainExtra();

        $main = $this->canShare($main);

        return array_merge($main, [
            'can_upload_media' => $policy->uploadMedias($context, $this->resource),
        ]);
    }

    protected function canShare(array $extra): array
    {
        $share = Arr::get($extra, ACL::CAN_SHARE, true);

        if (!$share) {
            return $extra;
        }

        Arr::set($extra, ACL::CAN_SHARE, $this->resource->total_item > 0);

        return $extra;
    }
}
