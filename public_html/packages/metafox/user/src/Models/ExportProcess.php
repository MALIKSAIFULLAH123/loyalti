<?php

namespace MetaFox\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class ExportProcess
 *
 * @property int    $id
 * @property string $path
 * @property string $filename
 * @property string $status
 * @property array  $filters
 * @property array  $properties
 * @property int    $user_id
 * @property string $user_type
 * @property string $created_at
 * @property string $updated_at
 * @property int    $total_user
 * @property string $download_url
 */
class ExportProcess extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'user_export_process';

    protected $table = 'user_export_processes';

    const STORAGE_SERVICE = 'local';
    /** @var string[] */
    protected $fillable = [
        'filename',
        'path',
        'status',
        'filters',
        'properties',
        'user_id',
        'user_type',
        'total_user',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filters'    => 'array',
        'properties' => 'array',
    ];

    public function getDownloadUrlAttribute(): string
    {
        return getFilePath($this->path, self::STORAGE_SERVICE);
    }
}

// end
