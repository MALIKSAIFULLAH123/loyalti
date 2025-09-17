<?php

namespace MetaFox\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Group\Database\Factories\RuleFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Rule.
 *
 * @property int    $id
 * @property int    $group_id
 * @property string $title
 * @property string $description
 * @property int    $ordering
 * @property Group  $group
 * @method   static RuleFactory factory(...$parameters)
 */
class Rule extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'group_rule';

    protected $table = 'group_rules';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'group_id',
        'title',
        'description',
        'ordering',
    ];

    /**
     * @return RuleFactory
     */
    protected static function newFactory(): RuleFactory
    {
        return RuleFactory::new();
    }

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id')->withTrashed();
    }
}

// end
