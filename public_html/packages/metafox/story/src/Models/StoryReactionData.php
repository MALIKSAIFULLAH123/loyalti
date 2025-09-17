<?php

namespace MetaFox\Story\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasItemMorph;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class StoryReactionData
 *
 * @property int    $id
 * @property int    $story_reaction_id
 * @property int    $item_id
 * @property string $item_type
 */
class StoryReactionData extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasItemMorph;

    public const ENTITY_TYPE = 'story_reaction_data';
    public const ITEM_TYPE_DEFAULT = 'preaction';
    public $timestamps = false;

    protected $table = 'story_reaction_data';

    /** @var string[] */
    protected $fillable = [
        'story_reaction_id',
        'item_id',
        'item_type',
    ];

}

// end
