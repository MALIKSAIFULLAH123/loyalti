<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Interface HasCoverMorph
 *
 * @property  MorphTo $covers
 * @property  int     $cover_id
 * @property  string  $cover_type
 * @property  string  $cover_photo_position
 * @package MetaFox\Platform\Contracts
 */
interface HasCoverMorph extends HasCover
{
    /**
     * @return int
     */
    public function getCoverId(): int;

    /**
     * @return string
     */
    public function getCoverType(): string;

    /**
     * @return MorphTo
     */
    public function cover(): morphTo;

    /**
     * @return array<string, mixed>
     */
    public function getCoverDataEmpty(): array;
}
