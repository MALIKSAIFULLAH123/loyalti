<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Trait HasCoverAsAvatarMorph.
 *
 * @mixin HasRelationships
 * @property morphTo $avatar
 * @property int     $cover_id
 * @property string  $cover_type
 * @property string  $cover_file_id
 */
trait HasCoverAsAvatarMorph
{
    use HasAvatarTrait;

    public function avatar(): morphTo
    {
        return $this->morphTo('photo', 'cover_type', 'cover_id')->withDefault(false);
    }

    public function getAvatarId(): ?int
    {
        return $this->cover_id;
    }

    public function getAvatarType(): ?string
    {
        return $this->cover_type;
    }
}
