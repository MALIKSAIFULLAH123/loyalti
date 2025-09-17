<?php

namespace MetaFox\Importer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Importer\Database\Factories\EntryFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Entry.
 *
 * @property int     $id
 * @property int     $bundle_id
 * @property int     $priority
 * @property ?int    $resource_id
 * @property ?string $resource_type
 * @property string  $ref_id
 * @property string  $source
 * @property string  $status
 * @property string  $created_at
 * @property int     $total_retry
 * @property string  $filename
 * @property int     $entry_index
 * @property string  $updated_at
 * @method   static EntryFactory factory(...$parameters)
 */
class Entry extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'MigrateEntry';

    protected $table = 'importer_entries';

    /** @var string[] */
    protected $fillable = [
        'bundle_id',
        'resource_id',
        'resource_type',
        'source',
        'ref_id',
        'status',
        'total_retry',
        'priority',
        'filename',
        'entry_index',
        'created_at',
        'updated_at',
    ];

    public function getResource(): mixed
    {
        return ResourceGate::getItem($this->resource_type, $this->resource_id);
    }

    /**
     * @return EntryFactory
     */
    protected static function newFactory()
    {
        return EntryFactory::new();
    }
}

// end
