<?php

namespace MetaFox\SEO\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class Schema
 *
 * @property int    $id
 * @property int    $meta_id
 * @property array  $schema
 * @property array  $schema_default
 * @property bool   $is_modified
 * @property string $created_at
 * @property string $updated_at
 * @property Meta   $meta
 *
 */
class Schema extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'core_seo_meta_schema';

    protected $table = 'core_seo_meta_schema';

    /** @var string[] */
    protected $fillable = [
        'meta_id',
        'schema',
        'schema_default',
        'is_modified',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'schema'         => 'array',
        'schema_default' => 'array',
    ];

    public function schema(): HasOne
    {
        return $this->hasOne(Meta::class, 'id', 'meta_id');
    }
}

// end
