<?php

namespace MetaFox\Profile\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Profile\Database\Factories\OptionFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * class Option.
 *
 * @property int    $id
 * @property int    $field_id
 * @property int    $ordering
 * @property string $label
 * @property string $label_var
 * @method static OptionFactory factory(...$parameters)
 */
class Option extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'user_custom_option';

    protected $table = 'user_custom_options';

    protected $translatableAttributes = [
        'label',
    ];
    /** @var string[] */
    protected $fillable = [
        'field_id',
        'label',
        'ordering',
        'created_at',
        'updated_at',
    ];

    /**
     * @return OptionFactory
     */
    protected static function newFactory()
    {
        return OptionFactory::new();
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class, 'field_id', 'id');
    }

    public function getLabelVarAttribute(): string
    {
        return Arr::get($this->attributes, 'label');
    }

    public function getLabelAttribute($value): string
    {
        return __($value);
    }
}

// end
