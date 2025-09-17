<?php

namespace MetaFox\StaticPage\Models;

use MetaFox\StaticPage\Models\StaticPage;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;

/**
 * Class StaticPageContent.
 *
 * @property int        $id
 * @property string     $text
 * @property string     $locale
 * @property string     $created_at
 * @property string     $updated_at
 * @property StaticPage $resource
 */
class StaticPageContent extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'static_page_content';

    protected $table = 'static_page_contents';

    /** @var string[] */
    protected $fillable = [
        'text',
        'static_page_id',
        'locale',
        'created_at',
        'updated_at',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(StaticPage::class, 'static_page_id', 'id');
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return '';
    }

    public function getAdminEditUrlAttribute(): string
    {
        return '';
    }
}

// end
