<?php

namespace MetaFox\Activity\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFox\Activity\Database\Factories\AttachmentFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Attachment.
 * @property int                         $id
 * @property int                         $user_id
 * @property string                      $user_type
 * @property int                         $owner_id
 * @property string                      $owner_type
 * @property int                         $privacy
 * @property int                         $total_like
 * @property int                         $total_comment
 * @property int                         $total_share
 * @property int                         $total_item
 * @property string                      $content
 * @property Collection|AttachmentData[] $items
 * @mixin Builder
 */
class Attachment extends Model
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE = 'attachment';

    protected $table = 'activity_attachments';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'privacy',
        'total_like',
        'total_comment',
        'total_share',
        'total_item',
        'content',
    ];

    /**
     * @param  array<string, mixed> $parameters
     * @return AttachmentFactory
     */
    public static function factory(array $parameters = [])
    {
        return AttachmentFactory::new($parameters);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AttachmentData::class, 'attachment_id', 'id');
    }
}

// end
