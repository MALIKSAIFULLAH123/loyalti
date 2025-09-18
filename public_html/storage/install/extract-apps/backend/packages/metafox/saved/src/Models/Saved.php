<?php

namespace MetaFox\Saved\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\HasPolicy;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserAsOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Saved\Database\Factories\SavedFactory;
use MetaFox\Saved\Repositories\SavedListItemViewRepositoryInterface;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;

/**
 * Class Saved.
 *
 * @mixin Builder
 * @property        int                      $id
 * @property        int                      $user_id
 * @property        string                   $user_type
 * @property        int                      $item_id
 * @property        string                   $item_type
 * @property        ?SavedList               $default_collection
 * @property        int                      $is_opened
 * @property        string                   $created_at
 * @property        string                   $updated_at
 * @property        array                    $collection_ids
 * @property        Collection|BelongsToMany $savedLists
 * @method   static SavedFactory             factory(...$parameters)
 */
class Saved extends Model implements ActionEntity, HasPolicy
{
    use HasEntity;
    use HasFactory;
    use HasItemMorph;
    use HasUserMorph;
    use HasUserAsOwnerMorph;
    use HasNestedAttributes;

    public const ENTITY_TYPE = 'saved';

    protected $table = 'saved_items';

    /**
     * @var string[]
     */
    public array $nestedAttributes = ['savedLists'];

    protected $fillable = [
        'user_id',
        'user_type',
        'item_id',
        'item_type',
        'is_opened',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_opened' => 'boolean',
    ];

    protected static function newFactory(): SavedFactory
    {
        return SavedFactory::new();
    }

    public function savedLists(): BelongsToMany
    {
        return $this->belongsToMany(
            SavedList::class,
            'saved_list_data',
            'saved_id',
            'list_id'
        )->using(SavedListData::class);
    }

    public function getItemTypeName(): string
    {
        $savedItem = $this->item;

        if (!$savedItem instanceof HasSavedItem) {
            return '';
        }

        return Arr::get($savedItem->toSavedItem(), 'item_type_name');
    }

    public function getDefaultCollectionAttribute(): ?SavedList
    {
        $savedListFirst = null;
        $savedLists     = $this->savedLists;
        if ($savedLists->count()) {
            /** @var SavedList $savedListFirst */
            $savedListFirst = $savedLists->first();
        }

        return $savedListFirst;
    }

    public function getCollectionIdsAttribute(): array
    {
        $collectionIds = [];
        $savedLists    = $this->savedLists;
        if ($savedLists->count()) {
            $collectionIds = $savedLists->pluck('id')->toArray();
        }

        return $collectionIds;
    }

    public function isOpened(User $context, ?int $collectionId = null): bool
    {
        if ($collectionId !== null) {
            return resolve(SavedListItemViewRepositoryInterface::class)
                ->isExists($context, [
                    'collection_id' => $collectionId,
                    'saved_id'      => $this->entityId(),
                ]);
        }

        $repository = resolve(SavedRepositoryInterface::class);

        return $repository->buildQuerySavedItemUnopened($context, $this)->get()->isEmpty();
    }
}
