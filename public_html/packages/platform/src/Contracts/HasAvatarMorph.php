<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Interface HasAvatar.
 *
 * Determine a resource has a privacy field.
 *
 * @property MorphTo $avatar
 * @property int     $avatar_id
 * @property string  $avatar_type
 */
interface HasAvatarMorph extends HasAvatar
{
    /**
     * @return MorphTo
     */
    public function avatar(): morphTo;

    /**
     * @return int|null
     */
    public function getAvatarId(): ?int;

    /**
     * @return string|null
     */
    public function getAvatarType(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function getAvatarDataEmpty(): array;
}
