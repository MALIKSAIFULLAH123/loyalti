<?php

namespace MetaFox\Saved\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Support\Eloquent\Appends\AppendPrivacyListTrait;
use MetaFox\Platform\Support\HasContent;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserAsOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Saved\Database\Factories\SavedListFactory;

/**
 * Class SavedList.
 *
 * @property        int              $id
 * @property        int              $saved_id
 * @property        string           $name
 * @property        string           $created_at
 * @property        string           $updated_at
 * @property        Collection       $savedItems
 * @property        Saved            $savedThumb
 * @property        int              $privacy
 * @property        string           $item_added_at
 * @method   static SavedListFactory factory(...$parameters)
 */
class SavedList extends Model implements Content, HasPrivacy
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasContent;
    use HasUserAsOwnerMorph;
    use AppendPrivacyListTrait;

    public const ENTITY_TYPE         = 'saved_list';
    public const MAXIMUM_NAME_LENGTH = 128;

    protected $table = 'saved_lists';

    protected $fillable = [
        'user_id',
        'user_type',
        'name',
        'saved_id',
        'privacy',
        'item_added_at',
    ];

    protected static function newFactory(): SavedListFactory
    {
        return SavedListFactory::new();
    }

    public function savedItems(): BelongsToMany
    {
        return $this->belongsToMany(
            Saved::class,
            'saved_list_data',
            'list_id',
            'saved_id'
        )->using(SavedListData::class);
    }

    public function savedListMembers(): HasMany
    {
        return $this->hasMany(SavedListMember::class, 'list_id', 'id');
    }

    public function savedThumb(): BelongsTo
    {
        return $this->belongsTo(Saved::class, 'saved_id', 'id');
    }

    public function toTitle(): string
    {
        return $this->name;
    }
    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("saved/list/$this->id");
    }
    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("saved/list/$this->id");
    }
}
