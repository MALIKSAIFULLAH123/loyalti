<?php

namespace MetaFox\Storage\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\TempFileModel;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Storage\Database\Factories\FileFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class File.
 * @property        string      $target
 * @property        int         $origin_id
 * @property        int         $is_origin
 * @property        string      $variant
 * @property        int         $storage_id
 * @property        string      $original_name
 * @property        string      $file_size
 * @property        string      $mime_type
 * @property        string      $extension
 * @property        string      $user_id
 * @property        string      $user_type
 * @property        string      $item_type
 * @property        string      $item_id
 * @property        int         $width
 * @property        int         $height
 * @property        string      $created_at
 * @property        string      $updated_at
 * @property        int         $id
 * @property        string      $path
 * @property        string      $url
 * @property        bool        $is_image
 * @property        array       $images
 * @method   static FileFactory factory(...$parameters)
 */
class StorageFile extends Model implements Entity, TempFileModel
{
    use HasEntity;
    use HasFactory;
    use HasItemMorph;
    use HasUserMorph;
    use SoftDeletes;

    public const ENTITY_TYPE = 'storage_file';

    protected $table = 'storage_files';

    /** @var string[] */
    protected $fillable = [
        'storage_id',
        'target',
        'origin_id',
        'is_origin',
        'variant',
        'user_id',
        'user_type',
        'item_type',
        'item_id',
        'original_name',
        'file_size',
        'path',
        'mime_type',
        'extension',
        'width',
        'height',
        'created_at',
        'updated_at',
    ];

    /**
     * @return FileFactory
     */
    protected static function newFactory()
    {
        return FileFactory::new();
    }

    protected static function booted()
    {
        static::creating(function (self $file) {
            if (!$file->variant) {
                $file->variant = 'origin';
            }

            if (!$file->target) {
                $file->target = app('storage')->getTarget($file->storage_id);
            }
        });

        static::created(function (self $file) {
            if (!$file->origin_id) {
                $file->origin_id = $file->id;
                $file->is_origin = 1;
            }
            $file->save();
        });
    }

    public function getServerIdAttribute(): string
    {
        return $this->storage_id;
    }

    public function getFileNameAttribute(): string
    {
        return $this->original_name;
    }

    public function getUrlAttribute(): string
    {
        $config = config('filesystems.disks.' . $this->target) ?? [];
        if (!Arr::has($config, 'driver')) {
            // the configuration no longer existed.
            return '';
        }

        return Storage::disk($this->target)->url($this->path);
    }

    public function getIsImageAttribute(): bool
    {
        return preg_match('/image/', $this->mime_type, $match) === 1;
    }

    public function getImagesAttribute(): array
    {
        return app('storage')->getUrls($this->origin_id);
    }

    /**
     * @param  mixed $source
     * @param  mixed $target
     * @param  bool  $trash
     * @return void
     */
    public function transfer(mixed $source, mixed $target, bool $trash): void
    {
    }

    public function rollUp(): void
    {
    }

    public function getImageAttribute(): ?string
    {
        return $this->url;
    }

    public function getImagesAttributes(): ?array
    {
        return app('storage')->getUrl($this->origin_id);
    }

    public function rollDown(): void
    {
        $this->delete();
    }

    public function getIsOriginAttribute(): bool
    {
        return $this->origin_id === $this->id;
    }

    public function isOrigin(): bool
    {
        return $this->origin_id === $this->id;
    }

    public function attach(string $storage): void
    {
        /** @var Collection<StorageFile> $files */
        $files    = app('storage')->getByOriginals($this->origin_id);
        $configId = app('storage')->getTarget($storage);
        foreach ($files as $file) {
            $file->storage_id = $storage;
            $file->target     = $configId;
            $file->saveQuietly();
        }
    }
}

// end
