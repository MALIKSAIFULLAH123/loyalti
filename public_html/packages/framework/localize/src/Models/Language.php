<?php

namespace MetaFox\Localize\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Database\Factories\LanguageFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Language.
 *
 * @property        int             $id
 * @property        string          $language_code
 * @property        string          $name
 * @property        ?string         $name_alias
 * @property        string          $charset
 * @property        string          $direction
 * @property        int             $is_default
 * @property        int             $is_active
 * @property        int             $is_master
 * @property        string          $updated_at
 * @property        string          $created_at
 * @property        ?string         $package_id
 * @method   static LanguageFactory factory(...$parameters)
 */
class Language extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'language';

    protected $table = 'core_languages';

    /** @var string[] */
    protected $fillable = [
        'language_code',
        'package_id',
        'name',
        'name_alias',
        'charset',
        'direction',
        'is_default',
        'is_active',
        'is_master',
        'store_id',
        'updated_at',
        'created_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_active' => 'boolean',
        'is_master' => 'boolean',
    ];

    protected $appends = ['is_default'];

    /**
     * @return LanguageFactory
     */
    protected static function newFactory()
    {
        return LanguageFactory::new();
    }

    public function getIsDefaultAttribute(): bool
    {
        return Settings::get('localize.default_locale', 'en') === $this->language_code;
    }

    public function getNameAttribute(string $value): string
    {
        return !empty($this->name_alias) ? $this->name_alias : $value;
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        return url_utility()->makeApiUrl('localize/phrase/browse?locale=' . $this->language_code);
    }
}

// end
