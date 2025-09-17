<?php

namespace MetaFox\Search\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Search\Database\Factories\SearchFactory;

/**
 * Class Search.
 *
 * @property        string        $title
 * @property        string        $text
 * @property        string        $item_type
 * @property        int           $item_id
 * @property        string        $user_type
 * @property        int           $user_id
 * @property        string        $owner_type
 * @property        int           $owner_id
 * @property        int           $id
 * @property        int           $privacy
 * @property        string        $created_at
 * @method   static SearchFactory factory(...$parameters)
 * @method          self          terms(string $string)
 * @mixin Builder
 */
class Search extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;
    use HasItemMorph;

    public const ENTITY_TYPE = 'search';

    protected $table = 'search_items';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'user_id',
        'user_type',
        'owner_id',
        'owner_type',
        'title',
        'text',
        'privacy',
        'created_at',
        'status',
    ];

    /**
     * @return SearchFactory
     */
    protected static function newFactory()
    {
        return SearchFactory::new();
    }

    /**
     * @param mixed  $query
     * @param string $term
     *
     * @return mixed
     */
    public function scopeTerms($query, string $term)
    {
        if (!$term) {
            return $query;
        }

        $driver = database_driver();

        if ($driver == 'pgsql') {
            return $query->whereRaw('search_text @@ plainto_tsquery(\'english\', ?)', [$term]);
        }

        return $query->whereRaw('MATCH (title, text) AGAINST (? IN BOOLEAN MODE)', $this->fullTextWildcards($term));
    }

    private function fullTextWildcards(string $term): string
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term            = str_replace($reservedSymbols, '', $term);

        $words = explode(' ', $term);

        foreach ($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if (strlen($word) >= 2) {
                $words[$key] = '+' . $word . '*';
            }
        }

        return implode(' ', $words);
    }

    /**
     * @return HasMany
     */
    public function privacyStreams(): HasMany
    {
        return $this->hasMany(PrivacyStream::class, 'item_id');
    }

    /**
     * @return BelongsToMany
     */
    public function tagData(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'search_tag_data',
            'item_id',
            'tag_id'
        )->using(TagData::class);
    }
}
