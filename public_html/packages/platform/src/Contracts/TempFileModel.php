<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Interface TempFileModel.
 *
 * @property int         $id
 * @property string      $item_type
 * @property int         $user_id
 * @property string      $user_type
 * @property string      $file_name
 * @property string      $original_name
 * @property string      $dir_name
 * @property string      $path
 * @property int         $file_size
 * @property string      $extension
 * @property string      $mime_type
 * @property string      $server_id
 * @property int         $width
 * @property int         $height
 * @property string      $created_at
 * @property string      $updated_at
 * @property string|null $thumbnail_sizes
 * @property string|null $square_thumbnail_sizes
 * @mixin HasUserMorph
 * @mixin Builder
 */
interface TempFileModel extends Entity
{
}
