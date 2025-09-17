<?php

namespace MetaFox\Music\Support\Browse\Traits\Song;

use MetaFox\Music\Models\Song;
use MetaFox\Music\Policies\SongPolicy;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;

trait ExtraTrait
{
    use HasExtra {
        getExtra as getMainExtra;
    }

    public function getExtra(): array
    {
        /**
         * @var SongPolicy $policy
         */
        $policy = PolicyGate::getPolicyFor(Song::class);

        if (!$policy) {
            return [];
        }

        $context = user();

        return array_merge($this->getMainExtra(), [
            'can_download'        => $policy->download($context, $this->resource),
            'can_add_to_playlist' => $policy->addToPlaylist($context, $this->resource),
        ]);
    }
}
