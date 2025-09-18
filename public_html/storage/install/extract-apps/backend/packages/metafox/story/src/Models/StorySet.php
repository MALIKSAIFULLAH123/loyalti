<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class StorySet
 *
 * @property int        $id
 * @property int        $user_id
 * @property string     $user_type
 * @property int        $auto_archive
 * @property int        $expired_at
 * @property string     $created_at
 * @property string     $updated_at
 * @property Collection $stories
 */
class StorySet extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasUserMorph;

    public const ENTITY_TYPE = 'story_set';

    protected $table = 'story_sets';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'user_type',
        'auto_archive',
        'expired_at',
        'created_at',
        'updated_at',
    ];

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class, 'set_id', 'id');
    }
}

// end
