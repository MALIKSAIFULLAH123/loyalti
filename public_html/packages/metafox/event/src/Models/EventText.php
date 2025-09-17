<?php

namespace MetaFox\Event\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

class EventText extends Model implements ResourceText
{
    use HasEntity;

    public const ENTITY_TYPE = 'event_text';

    /**
     * @var bool
     */
    public $timestamps = false;

    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'event_text';

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'text',
        'text_parsed',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'id', 'id');
    }
}
