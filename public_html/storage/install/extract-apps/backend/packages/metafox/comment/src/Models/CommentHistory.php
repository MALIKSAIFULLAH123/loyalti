<?php

namespace MetaFox\Comment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph as HasItemMorphModel;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class CommentHistory.
 *
 * @property int         $id
 * @property string      $content
 * @property string      $created_at
 * @property string      $updated_at
 * @property string      $params
 * @property string|null $image_path
 * @property string      $server_id
 * @property string      $phrase
 * @property string      $text_parsed
 * @property string      $description
 * @property Comment     $comment
 */
class CommentHistory extends Model implements
    Entity,
    HasItemMorph,
    HasHashTag
{
    use HasEntity;
    use HasUserMorph;
    use HasItemMorphModel;

    public const ENTITY_TYPE = 'comment_histories';

    protected $table = 'comment_histories';

    public const PHRASE_ADDED_PHOTO       = 'comment_add_photo';
    public const COMMENT_DELETED_PHOTO    = 'comment_delete_photo';
    public const COMMENT_UPDATED_PHOTO    = 'comment_update_photo';
    public const PHRASE_ADDED_STICKER     = 'comment_add_sticker';
    public const PHRASE_DELETED_STICKER   = 'comment_delete_sticker';
    public const PHRASE_UPDATED_STICKER   = 'comment_update_sticker';
    public const PHRASE_ADDED_GIF         = 'comment_add_gif';
    public const PHRASE_UPDATED_GIF       = 'comment_update_gif';
    public const PHRASE_DELETED_GIF       = 'comment_delete_gif';
    /**
     * @var array<string, mixed>
     */
    protected $casts = [
        'tagged_user_ids' => 'array',
    ];
    /** @var string[] */
    protected $fillable = [
        'comment_id',
        'user_id',
        'user_type',
        'item_id',
        'item_type',
        'content',
        'params',
        'phrase',
        'tagged_user_ids',
        'created_at',
    ];

    public function getTextParsedAttribute(): string
    {
        return $this->content;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->phrase ? __p("comment::phrase.history.{$this->phrase}") : null;
    }

    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'comment_history_tag_data',
            'item_id',
            'tag_id'
        )->using(CommentHistoryTagData::class);
    }
}

// end
