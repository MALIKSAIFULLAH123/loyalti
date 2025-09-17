<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

use MetaFox\Platform\Contracts\HasAvatar;

/**
 * Trait HasAvatarTrait.
 * @mixin HasAvatar
 * @property string $avatar_file_id
 */
trait HasAvatarTrait
{
    public function getAvatarSizes(): array
    {
        return ['50x50', '120x120', '200x200'];
    }

    public function getAvatarAttribute(): ?string
    {
        return app('storage')->getUrl($this->avatar_file_id);
    }

    public function getAvatarsAttribute(): ?array
    {
        return app('storage')->getUrls($this->avatar_file_id);
    }
}
