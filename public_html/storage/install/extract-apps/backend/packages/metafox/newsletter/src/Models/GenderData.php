<?php

namespace MetaFox\Newsletter\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class GenderData.
 *
 * @property int $id
 */
class GenderData extends Pivot implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'newsletter_gender_data';

    protected $table = 'newsletter_gender_data';

    protected $foreignKey = 'newsletter_id';

    protected $relatedKey = 'gender_id';

    /** @var string[] */
    protected $fillable = [
        'newsletter_id',
        'gender_id',
    ];
}

// end
