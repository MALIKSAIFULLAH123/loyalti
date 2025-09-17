<?php

namespace MetaFox\Follow\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Follow\Database\Factories\FollowFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasOwnerMorph;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stubfactory.
 */

/**
 * Class Follow.
 * @mixin Builder
 * @method static FollowFactory factory(...$parameters)
 */
class Follow extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;
    use HasOwnerMorph;

    public const ENTITY_TYPE    = 'follow';

    /** @var string[] */
    protected $fillable = [];
}

// end
