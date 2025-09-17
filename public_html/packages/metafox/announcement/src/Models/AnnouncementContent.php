<?php

namespace MetaFox\Announcement\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Announcement\Database\Factories\AnnouncementContentFactory;

/**
 * Class AnnouncementContent.
 *
 * @property int          $id
 * @property string       $text
 * @property string       $text_parsed
 * @property string       $locale
 * @property string       $created_at
 * @property string       $updated_at
 * @property Announcement $resource
 */
class AnnouncementContent extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'announcement_content';

    protected $table = 'announcement_contents';

    /** @var string[] */
    protected $fillable = [
        'text',
        'text_parsed',
        'announcement_id',
        'locale',
        'created_at',
        'updated_at',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id', 'id');
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return '';
    }

    public function getAdminEditUrlAttribute(): string
    {
        return '';
    }
}

// end
