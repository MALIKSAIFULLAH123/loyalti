<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Database\Factories\AttachmentFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;
use MetaFox\Storage\Models\StorageFile;

/**
 * Class Attachment.
 *
 * @property        int               $id
 * @property        string            $item_type
 * @property        int               $user_id
 * @property        string            $user_type
 * @property        string            $file_name
 * @property        string            $original_name
 * @property        string            $dir_name
 * @property        string            $path
 * @property        int               $file_size
 * @property        StorageFile       $file
 * @property        string            $extension
 * @property        string            $mime_type
 * @property        string            $download_url
 * @property        string            $file_name_with_extension
 * @property        string            $server_id
 * @property        string            $file_id
 * @property        int               $width
 * @property        int               $height
 * @property        int               $is_image
 * @property        string            $created_at
 * @property        string            $updated_at
 * @method   static AttachmentFactory factory(...$parameters)
 */
class Attachment extends Model implements Entity, HasThumbnail
{
    use HasEntity;
    use HasFactory;
    use HasItemMorph;
    use HasThumbnailTrait;

    public const ENTITY_TYPE          = 'core_attachment';
    public const IMPORTER_ENTITY_TYPE = 'attachment';

    protected $table = 'core_attachments';

    public array $fileColumns = ['file_id' => 'attachment'];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_image' => 'boolean',
    ];

    /** @var string[] */
    protected $fillable = [
        'item_type',
        'item_id',
        'user_id',
        'user_type',
        'file_id',
    ];

    /**
     * @return AttachmentFactory
     */
    protected static function newFactory(): AttachmentFactory
    {
        return AttachmentFactory::new();
    }

    public function getThumbnail(): ?string
    {
        return $this->file_id;
    }

    /**
     * @return string|null
     */
    public function getDownloadUrlAttribute(): ?string
    {
        return $this->file?->url;
    }

    /**
     * @return StorageFile|null
     */
    public function getFileAttribute(): ?StorageFile
    {
        return app('storage')->getFile($this->file_id);
    }

    /**
     * @return string|null
     */
    public function getFileNameWithExtensionAttribute(): ?string
    {
        return $this->file?->original_name;
    }

    public function clone(User $context, string $itemType, int $itemId): self
    {
        $new = new self([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'item_type' => $itemType,
            'item_id'   => $itemId,
            'file_id'   => $this->file_id,
        ]);

        $new->save();

        $new->refresh();

        return $new;
    }
}

// end
