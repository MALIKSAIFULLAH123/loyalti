<?php

namespace MetaFox\Page\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class PageHistory
 *
 * @property int    $id
 * @property int    $page_id
 * @property int    $user_id
 * @property string $user_type
 * @property string $type
 * @property mixed  $extra
 * @property Page   $page
 */
class PageHistory extends Model implements Entity, IsNotifyInterface, HasUrl
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'page_history';

    protected $table = 'page_histories';

    /** @var string[] */
    protected $fillable = [
        'page_id',
        'user_id',
        'user_type',
        'type',
        'extra',
        'created_at',
        'updated_at',
    ];

    public function toNotification(): ?array
    {
        return null;
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id', 'id')->withTrashed();
    }

    public function toLink(): ?string
    {
        return $this->page?->toLink();
    }

    public function toUrl(): ?string
    {
        return $this->page?->toUrl();
    }

    public function toRouter(): ?string
    {
        return $this->page?->toRouter();
    }
}

// end
