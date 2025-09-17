<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MetaFox\Platform\Contracts\HasCoverMorph;

/**
 * Trait HasCoverMorph.
 * @mixin HasRelationships
 * @mixin HasCoverMorph
 * @property int    $cover_id
 * @property string $cover_type
 * @property string $cover_photo_position
 */
trait HasCoverMorphTrait
{
    use HasCoverTrait;

    public function cover(): morphTo
    {
        return $this->morphTo('photo', 'cover_type', 'cover_id')->withDefault(false);
    }

    public function getCoverId(): int
    {
        return (int) $this->cover_id;
    }

    public function getCoverType(): string
    {
        return (string) $this->cover_type;
    }

    public function getCoverDataEmpty(): array
    {
        return [
            'cover_id'             => 0,
            'cover_type'           => 'photo',
            'cover_file_id'        => null,
            'cover_photo_position' => null,
        ];
    }
}
