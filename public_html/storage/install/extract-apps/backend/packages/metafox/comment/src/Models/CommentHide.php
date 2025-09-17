<?php

namespace MetaFox\Comment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Comment\Database\Factories\CommentHideFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class CommentHide.
 * @property int    $id
 * @property int    $user_id
 * @property string $user_type
 * @property int    $item_id
 * @method   static CommentHideFactory factory(...$parameters)
 */
class CommentHide extends Model implements Entity
{
    use HasEntity;
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'comment_hidden';

    public $timestamps = false;

    protected $table = 'comment_hidden';

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'item_id',
        'type',
        'is_hidden',
    ];

    /**
     * @return CommentHideFactory
     */
    protected static function newFactory(): CommentHideFactory
    {
        return CommentHideFactory::new();
    }
}
