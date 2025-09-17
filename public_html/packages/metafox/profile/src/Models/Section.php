<?php

namespace MetaFox\Profile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Profile\Database\Factories\SectionFactory;
use MetaFox\Profile\Support\CustomField;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Section.
 *
 * @mixin Builder
 * @property        int        $id
 * @property        string     $name
 * @property        string     $label
 * @property        string     $description
 * @property        int        $ordering
 * @property        bool       $is_active
 * @property        bool       $is_system
 * @property        Collection $profiles
 * @property        Collection $fields
 * @method   static SectionFactory factory(...$parameters)
 */
class Section extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasNestedAttributes;

    public const ENTITY_TYPE = 'user_custom_section';

    protected $table = 'user_custom_sections';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'name',
        'label',
        'description',
        'is_active',
        'is_system',
        'ordering',
        'extra',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'extra'     => 'array',
    ];

    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'profiles',
    ];

    public function getLabelAttribute(): ?string
    {
        return htmlspecialchars_decode(__p('profile::phrase.' . $this->name . '_label'));
    }

    public function setLabelAttribute($value)
    {
        $key = 'profile::phrase.' . $this->name . '_label';

        if (!is_array($value)) {
            return;
        }

        $service = resolve(PhraseRepositoryInterface::class);

        foreach ($value as $locale => $text) {
            $service->updatePhraseByKey($key, htmlspecialchars($text), $locale);
        }
    }

    public function setDescriptionAttribute($value)
    {
        $key = 'profile::phrase.' . $this->name . '_description';

        if (!is_array($value)) {
            return;
        }

        $service = resolve(PhraseRepositoryInterface::class);

        foreach ($value as $locale => $text) {
            $service->updatePhraseByKey($key, htmlspecialchars($text), $locale);
        }
    }

    public function getDescription(): ?string
    {
        return htmlspecialchars_decode(__p('profile::phrase.' . $this->name . '_description'));
    }

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class, 'section_id', 'id');
    }

    /**
     * @return SectionFactory
     */
    protected static function newFactory()
    {
        return SectionFactory::new();
    }

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(
            Profile::class,
            'user_custom_structure',
            'section_id',
            'profile_id'
        )->using(Structure::class);
    }

    public function getAdminBrowseUrlAttribute(): string
    {
        $profileType = $this->profiles()->first()?->user_type;
        return match ($profileType) {
            CustomField::SECTION_TYPE_USER => url_utility()->makeApiUrl('profile/section/browse'),
            default                        => url_utility()->makeApiUrl("{$profileType}/section/browse"),
        };
    }

    public function getAdminEditUrlAttribute(): string
    {
        $profileType = $this->getUserType();
        return match ($profileType) {
            CustomField::SECTION_TYPE_USER => url_utility()->makeApiUrl("profile/section/edit/{$this->entityId()}"),
            default                        => url_utility()->makeApiUrl("{$profileType}/section/edit/{$this->entityId()}"),
        };
    }

    public function getUserType(): string
    {
        return $this->profiles()->first()?->user_type ?? CustomField::SECTION_TYPE_USER;
    }
}

// end
