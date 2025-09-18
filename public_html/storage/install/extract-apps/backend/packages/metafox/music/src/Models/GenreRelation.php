<?php

namespace MetaFox\Music\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class GenreRelation
 *
 * @property int $id
 */
class GenreRelation extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'music_genre_relation';

    protected $table = 'music_genre_relations';

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
