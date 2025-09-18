<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class VideoTagData.
 * @mixin Builder
 * @property int    $id
 * @property int    $item_id
 * @property int    $tag_id
 * @property string $tag_text
 */
class VideoTagData extends Pivot
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'video_tag_data';

    /**
     * @var string[]
     */
    protected $fillable = [
        'item_id',
        'tag_id',
    ];
}
