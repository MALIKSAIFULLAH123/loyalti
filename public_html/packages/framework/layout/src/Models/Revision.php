<?php

namespace MetaFox\Layout\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Layout\Database\Factories\RevisionFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Revision
 *
 * @property int     $id
 * @method static RevisionFactory factory(...$parameters)
 * @property int     $snippet_id
 * @property string  $name
 * @property array   $data
 * @property string  $created_at
 * @property string  $updated_at
 * @property Snippet $snippet
 */
class Revision extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'core_layout_revision';

    protected $table = 'layout_revisions';

    /** @var string[] */
    protected $fillable = [
        'snippet_id',
        'name',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function revert()
    {
        $snippet = $this->snippet;

        $snippet->data = $this->data;
        $snippet->name = $this->name;
        $snippet->revision_id = $this->id;

        $snippet->save();
    }

    /**
     * @return HasOne
     */
    public function snippet(): HasOne
    {
        return $this->hasOne(Snippet::class, 'id', 'snippet_id');
    }

    /**
     * @return RevisionFactory
     */
    protected static function newFactory()
    {
        return RevisionFactory::new();
    }
}

// end
