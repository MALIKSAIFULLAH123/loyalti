<?php

namespace MetaFox\Hashtag\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use MetaFox\Hashtag\Database\Factories\TagFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Traits\Eloquent\Model\HasAmountsTrait;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Tag.
 * @mixin Builder
 *
 * @property        int        $id
 * @property        string     $text
 * @property        string     $tag_url
 * @property        string     $tag_hyperlink
 * @property        int        $total_item
 * @method   static TagFactory factory(...$parameters)
 */
class Tag extends Model implements Entity, HasAmounts, HasUrl
{
    use HasFactory;
    use HasEntity;
    use HasAmountsTrait;

    public const ENTITY_TYPE = 'tag';

    protected $table = 'hashtag_tags';

    public $timestamps = false;

    protected $fillable = [
        'text',
        'tag_url',
        'total_item',
    ];

    /**
     * @return TagFactory
     */
    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }

    /**
     * @return string
     */
    public function getTagHyperlinkAttribute(): string
    {
        $hashtag = '#' . $this->text;

        return parse_output()->buildHashtagLink($hashtag, $this->tag_url);
    }

    public function toUrl(): ?string
    {
        return url_utility()->makeApiFullUrl("hashtag/search?q={$this->tag_url}");
    }
    public function toLink(): ?string
    {
        return url_utility()->makeApiUrl("hashtag/search?q={$this->tag_url}");
    }
    public function toRouter(): ?string
    {
        return url_utility()->makeApiMobileUrl("hashtag/search?q={$this->tag_url}");
    }
}
