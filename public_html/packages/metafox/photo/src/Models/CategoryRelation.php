<?php

namespace MetaFox\Photo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class CategoryRelation
 *
 * @property int $parent_id
 * @property int $child_id
 * @property int $depth
 */
class CategoryRelation extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'photo_category_relation';

    protected $table = 'photo_category_relations';
    public $incrementing = false;
    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'parent_id',
        'child_id',
        'depth',
    ];
}

// end
