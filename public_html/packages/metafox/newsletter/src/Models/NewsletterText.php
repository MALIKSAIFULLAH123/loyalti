<?php

namespace MetaFox\Newsletter\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class NewsletterText.
 *
 * @property int    $id
 * @property string $text_html
 * @property string $text
 * @property string $text_html_raw
 * @property string $text_raw
 * @mixin Builder
 */
class NewsletterText extends Model implements Entity
{
    use HasEntity;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'newsletter_text';

    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'newsletter_text';

    /**
     * @var array<string>
     */
    public array $translatableAttributes = [
        'text_html',
        'text',
    ];

    protected $fillable = [
        'id',
        'text_html',
        'text',
        'created_at',
        'updated_at',
    ];

    public function getTextHtmlAttribute(): ?string
    {
        return __p($this->text_html_raw);
    }

    public function getTextHtmlRawAttribute(): ?string
    {
        return Arr::get($this->attributes, 'text_html', '');
    }

    public function getTextAttribute(): ?string
    {
        return __p($this->text_raw);
    }

    public function getTextRawAttribute(): ?string
    {
        return Arr::get($this->attributes, 'text', '');
    }
}

// end
