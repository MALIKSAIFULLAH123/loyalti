<?php

namespace MetaFox\Rewrite\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Rewrite\Database\Factories\RuleFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Rule.
 *
 * @property int     $id
 * @property string  $from_path
 * @property string  $to_path
 * @property string  $to_mobile_path
 * @property ?string $module_id
 * @method   static  RuleFactory factory(...$parameters)
 */
class Rule extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'url_rewrite';

    protected $table = 'core_rewrite_rules';

    public $primaryKey = 'id';

    /** @var string[] */
    protected $fillable = [
        'from_path',
        'to_path',
        'to_mobile_path',
        'module_id',
    ];

    /**
     * @return RuleFactory
     */
    protected static function newFactory()
    {
        return RuleFactory::new();
    }
}

// end
