<?php

namespace MetaFox\Advertise\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Advertise\Database\Factories\LanguageFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Language.
 *
 * @property        int             $id
 * @method   static LanguageFactory factory(...$parameters)
 */
class Language extends Pivot implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'advertise_language';

    protected $table = 'advertise_languages';

    protected $foreignKey = 'item_id';

    protected $relatedKey = 'language_code';

    /** @var string[] */
    protected $fillable = [
        'item_id',
        'item_type',
        'language_code',
    ];

    /**
     * @return LanguageFactory
     */
    protected static function newFactory()
    {
        return LanguageFactory::new();
    }
}

// end
