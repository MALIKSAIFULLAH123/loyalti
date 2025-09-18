<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Group\Database\Factories\ExampleRuleFactory;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class ExampleRule.
 *
 * @property        int                $id
 * @property        string             $title
 * @property        string             $title_var
 * @property        string             $description
 * @property        string             $description_var
 * @property        int                $ordering
 * @property        int                $is_active
 * @method   static ExampleRuleFactory factory(...$parameters)
 */
class ExampleRule extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'group_rule_example';
    public const IS_ACTIVE   = 1;

    protected $table   = 'group_rule_examples';

    protected $translatableAttributes = [
        'title',
        'description',
    ];

    /** @var string[] */
    protected $fillable = [
        'title',
        'description',
        'is_active',
    ];

    /**
     * @return ExampleRuleFactory
     */
    protected static function newFactory(): ExampleRuleFactory
    {
        return ExampleRuleFactory::new();
    }

    public function getTitleVarAttribute(): string
    {
        return Arr::get($this->attributes, 'title') ?: '';
    }

    public function getDescriptionVarAttribute(): string
    {
        return Arr::get($this->attributes, 'description') ?: '';
    }

    public function getTitleAttribute(): string
    {
        return __p($this->title_var);
    }

    public function getDescriptionAttribute(): string
    {
        return __p($this->description_var);
    }
}

// end
