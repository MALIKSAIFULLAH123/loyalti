<?php

namespace MetaFox\Profile\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasNestedAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Profile\Database\Factories\ValueFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * class Value.
 *
 * @property int   $id
 * @property int   $field_id
 * @property mixed $field_value_text
 * @property int   $ordering
 * @property int   $privacy
 * @property Field $field
 * @method   static ValueFactory factory(...$parameters)
 */
class Value extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasNestedAttributes;

    public const ENTITY_TYPE = 'user_custom_value';

    protected $table = 'user_custom_value';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'field_id',
        'field_value_text',
        'ordering',
        'privacy',
    ];
    /**
     * @var array<string>|array<string, mixed>
     */
    public array $nestedAttributes = [
        'optionsData',
    ];

    /**
     * @return ValueFactory
     */
    protected static function newFactory()
    {
        return ValueFactory::new();
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class, 'field_id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function optionsData(): BelongsToMany
    {
        return $this->belongsToMany(
            Option::class,
            'user_custom_option_data',
            'item_id',
            'custom_option_id'
        )->using(OptionData::class);
    }

}

// end
