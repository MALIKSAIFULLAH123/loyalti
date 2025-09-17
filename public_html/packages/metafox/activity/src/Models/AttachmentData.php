<?php

namespace MetaFox\Activity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Activity\Database\Factories\AttachmentDataFactory;

/**
 * Class AttachmentData.
 * @property int        $id
 * @property int        $attachment_id
 * @property int        $item_id
 * @property string     $item_type
 * @property Attachment $attachment
 */
class AttachmentData extends Model
{
    use HasFactory;

    public const ENTITY_TYPE = 'attachment_data';

    protected $table = 'activity_attachment_data';

    /** @var string[] */
    protected $fillable = [
        'attachment_id',
        'item_id',
        'item_type',
    ];

    public $timestamps = false;

    /**
     * @param  array<string, mixed>  $parameters
     * @return AttachmentDataFactory
     */
    public static function factory(array $parameters = [])
    {
        return AttachmentDataFactory::new($parameters);
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'attachment_id', 'id');
    }
}

// end
