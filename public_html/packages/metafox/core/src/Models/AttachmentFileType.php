<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Database\Factories\AttachmentFileTypeFactory;
use MetaFox\Core\Support\Facades\AttachmentFileType as FacadesAttachmentFileType;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class AttachmentFileType.
 *
 * @property        int                       $id
 * @property        string                    $extension
 * @property        string                    $mime_type
 * @property        int                       $is_active
 * @method   static AttachmentFileTypeFactory factory(...$parameters)
 */
class AttachmentFileType extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'core_attachment_file_type';

    protected $table = 'core_attachment_file_types';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'extension',
        'mime_type',
        'is_active',
    ];

    /**
     * @return AttachmentFileTypeFactory
     */
    protected static function newFactory(): AttachmentFileTypeFactory
    {
        return AttachmentFileTypeFactory::new();
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return 'attachment/type/browse';
    }

    /**
     * The "booted" method of the model.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected static function booted(): void
    {
        static::created(function (self $type) {
            FacadesAttachmentFileType::clearCache();
        });

        static::deleted(function (self $type) {
            FacadesAttachmentFileType::clearCache();
        });
    }
}

// end
