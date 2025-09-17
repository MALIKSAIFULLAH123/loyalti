<?php

namespace MetaFox\Comment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Comment\Database\Factories\CommentAttachmentFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Storage\Models\StorageFile;

/**
 * Class CommentAttachment.
 * @property        int                      $id
 * @property        int                      $comment_id
 * @property        int                      $item_id
 * @property        string                   $item_type
 * @property        string                   $params
 * @property        string                   $image_url
 * @property        string                   $download_url
 * @property        string                   $image_file_id
 * @method   static CommentAttachmentFactory factory(...$parameters)
 */
class CommentAttachment extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasItemMorph;

    public $timestamps = false;

    public const ENTITY_TYPE = 'comment_attachments';

    public const TYPE_FILE     = 'storage_file';
    public const TYPE_STICKER  = 'sticker';
    public const TYPE_PREVIEW  = 'preview';
    public const TYPE_GIF      = 'gif';
    public const TYPE_LINK     = 'link';

    protected $table = 'comment_attachments';

    /** @var string[] */
    protected $fillable = [
        'comment_id',
        'item_id',
        'item_type',
        'params',
    ];

    // where to store resources ?
    public array $fileColumns = [
        'image_file_id' => 'photo',
    ];

    /**
     * @return CommentAttachmentFactory
     */
    protected static function newFactory(): CommentAttachmentFactory
    {
        return CommentAttachmentFactory::new();
    }

    /**
     * @return BelongsTo
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function getImageUrlAttribute(): string
    {
        return app('storage')->getFile($this->item_id)->url;
    }

    public function getDownloadUrlAttribute(): string
    {
        return app('storage')->getAs($this->item_id);
    }

    public function getImageFileIdAttribute(): ?string
    {
        if (StorageFile::ENTITY_TYPE === $this->item_type) {
            return $this->item_id;
        }

        return null;
    }
}

// end
