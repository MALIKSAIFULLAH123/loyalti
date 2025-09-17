<?php

namespace MetaFox\Forum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ForumPostQuote extends Model
{
    protected $fillable = [
        'post_id',
        'quote_id',
        'quote_user_type',
        'quote_user_id',
        'quote_content',
    ];

    public function quotedPost(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'quote_id');
    }

    public function quotedUser(): MorphTo
    {
        return $this->morphTo('quote', 'quote_user_type', 'quote_user_id', 'id');
    }
}
