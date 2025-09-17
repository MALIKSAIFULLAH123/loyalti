<?php

namespace MetaFox\Storage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Storage\Database\Factories\AssetFactory;
use Illuminate\Support\Str;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Asset.
 *
 * @property        int          $id
 * @property        string       $name
 * @property        string       $module_id
 * @property        string       $package_id
 * @property        int          $file_id
 * @property        string       $local_path
 * @property        ?string      $url
 * @property        ?string      $file_mime_type
 * @property        ?StorageFile $file
 * @method   static AssetFactory factory(...$parameters)
 */
class Asset extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'storage_asset';

    protected $table = 'storage_assets';

    /** @var string[] */
    protected $fillable = [
        'name',
        'module_id',
        'package_id',
        'file_id',
        'local_path',
    ];

    protected $appends = ['url', 'file_mime_type'];

    // where to store resources ?
    public array $fileColumns = [
        'file_id' => 'asset',
    ];

    /**
     * @return AssetFactory
     */
    protected static function newFactory()
    {
        return AssetFactory::new();
    }

    public function getUrlAttribute(): ?string
    {
        return app('storage')->getUrl($this->file_id);
    }

    public function getFileMimeTypeAttribute(): ?string
    {
        return app('storage')->getMimeType($this->file_id);
    }

    public function isModified(): bool
    {
        /**
         * In case the asset is initialized by empty file
         */
        if (null === $this->file_id && MetaFoxConstant::EMPTY_STRING === $this->local_path) {
            return false;
        }

        $isModified = true;

        try {
            $localPath = $this->local_path;

            if ($this->file instanceof StorageFile && MetaFoxConstant::EMPTY_STRING !== $localPath) {
                $isModified = !Str::is("*$localPath", $this->file->path);
            }
        } catch (\Throwable $th) {
            //Just silent the error
        }

        return $isModified;
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(StorageFile::class, 'file_id', 'id');
    }
}

// end
