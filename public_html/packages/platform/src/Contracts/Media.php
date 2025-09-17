<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface Media.
 * @mixin Model
 * @mixin Builder
 * @property int        $group_id
 * @property int        $album_id
 * @property bool       $is_processing
 * @property Content    $album
 * @property Content    $group
 * @property Model|null $groupItem
 * @property Model|null $albumItem
 *
 * This interface shall be used for media content like: photos, videos or songs.
 */
interface Media extends Content
{
}
