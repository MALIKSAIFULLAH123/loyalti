<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasAvatar.
 *
 * @property string|null                $avatar
 * @property array<string, string>|null $avatars
 */
interface HasAvatar
{
    /**
     * @return string|null
     */
    public function getAvatarAttribute(): ?string;

    /**
     * @return array<string, mixed>|null
     */
    public function getAvatarsAttribute(): ?array;
}
