<?php

namespace MetaFox\Friend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Friend\Database\Factories\FriendSuggestionIgnoreFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class FriendSuggestionIgnore.
 *
 * @property int    $id
 * @method   static FriendSuggestionIgnoreFactory factory(...$parameters)
 */
class FriendSuggestionIgnore extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE = 'friend_suggestion_ignore';

    protected $table = 'friend_suggestion_ignore';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
    ];

    /**
     * @return FriendSuggestionIgnoreFactory
     */
    protected static function newFactory(): FriendSuggestionIgnoreFactory
    {
        return FriendSuggestionIgnoreFactory::new();
    }
}

// end
