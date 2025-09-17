<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MetaFox\Photo\Database\Factories\PhotoGroupItemFactory;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;

/**
 * Class PhotoGroupItem.
 * @mixin Builder
 * @property int        $id
 * @property int        $group_id
 * @property PhotoGroup $group
 * @property Content    $detail
 * @property string     $item_type
 * @property int        $ordering
 * @property string     $created_at
 * @property string     $updated_at
 * @property bool       $is_approved
 * @method   static     PhotoGroupItemFactory factory()
 */
class PhotoGroupItem extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasItemMorph;

    public const ENTITY_TYPE = 'photo_set_item';

    protected $table = 'photo_group_items';

    protected $fillable = [
        'group_id',
        'item_type',
        'item_id',
        'ordering',
        'created_at',
        'updated_at',
        'is_approved',
    ];

    public $casts = [
        'is_approved' => 'boolean'
    ];

    /**
     * @var string[]
     */
    protected $with = ['detail'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(PhotoGroup::class);
    }

    public function getGroupAttribute()
    {
        return LoadReduce::getEntity(PhotoGroup::ENTITY_TYPE, $this->group_id, fn() => $this->getRelationValue('group'));
    }

    public function getDetailAttribute(){
        return LoadReduce::getEntity($this->item_type, $this->item_id, fn() => $this->getRelationValue('detail'));
    }

    public function detail(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }

    protected static function newFactory(): PhotoGroupItemFactory
    {
        return PhotoGroupItemFactory::new();
    }

    public function isApproved(): bool
    {
        return $this->is_approved;
    }
}
