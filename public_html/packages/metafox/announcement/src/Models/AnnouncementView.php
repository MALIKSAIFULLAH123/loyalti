<?php

namespace MetaFox\Announcement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Announcement\Database\Factories\AnnouncementViewFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class AnnouncementView.
 *
 * @mixin Builder
 *
 * @property int               $id
 * @property Announcement|null $announcement
 * @property string            $created_at
 * @property string            $updated_at
 * @method   static            AnnouncementViewFactory factory(...$parameters)
 */
class AnnouncementView extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'announcement_view';

    protected $table = 'announcement_views';

    /** @var string[] */
    protected $fillable = [
        'announcement_id',
        'user_id',
        'user_type',
        'created_at',
        'updated_at',
    ];

    protected static function newFactory(): AnnouncementViewFactory
    {
        return AnnouncementViewFactory::new();
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id', 'id');
    }
}

// end
