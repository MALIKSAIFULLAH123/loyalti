<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MetaFox\Photo\Database\Factories\AlbumItemFactory;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class AlbumItem.
 * @mixin Builder
 *
 * @property int          $id
 * @property int          $album_id
 * @property int          $group_id
 * @property Album        $album
 * @property PhotoGroup   $group
 * @property Content|null $detail
 * @property string       $item_type
 * @property int          $item_id
 * @property int          $ordering
 * @property string       $created_at
 * @property string       $updated_at
 * @property bool         $is_approved
 *
 * @method static AlbumItemFactory factory()
 */
class AlbumItem extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'photo_album_item';

    protected $table = 'photo_album_item';

    /**
     * @var string[]
     */
    protected $with = ['detail'];

    protected $fillable = [
        'album_id',
        'group_id',
        'item_type',
        'item_id',
        'ordering',
        'created_at',
        'updated_at',
        'is_approved',
    ];

    public $casts = [
        'is_approved' => 'boolean',
    ];

    public function detail(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }

    /**
     * @return BelongsTo
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(PhotoGroup::class, 'group_id', 'id');
    }

    protected static function newFactory(): AlbumItemFactory
    {
        return AlbumItemFactory::new();
    }
}
