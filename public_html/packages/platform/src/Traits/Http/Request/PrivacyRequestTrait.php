<?php

namespace MetaFox\Platform\Traits\Http\Request;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Trait HasFeedParam.
 * @property Content $resource
 */
trait PrivacyRequestTrait
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function handlePrivacy(array $data): array
    {
        if (!isset($data['privacy'])) {
            $data['privacy'] = MetaFoxPrivacy::EVERYONE;
        }

        $data['list'] = [];

        if (is_array($data['privacy'])) {
            $data['list']    = $data['privacy'];
            $data['privacy'] = MetaFoxPrivacy::CUSTOM;
        }

        return $data;
    }
}
