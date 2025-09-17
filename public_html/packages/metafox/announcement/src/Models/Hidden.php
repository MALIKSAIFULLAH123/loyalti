<?php

namespace MetaFox\Announcement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use MetaFox\Announcement\Database\Factories\HiddenFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;

/**
 * Class Hidden.
 *
 * @mixin Builder
 *
 * @method static HiddenFactory factory()
 */
class Hidden extends Model implements Entity
{
    use HasEntity;
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE = 'announcement_hidden';

    protected $table = 'announcement_hidden';

    protected $fillable = [
        'user_id',
        'user_type',
        'announcement_id',
    ];

    /**
     * @param  array<string, mixed> $parameters
     * @return HiddenFactory
     */
    public static function newFactory(array $parameters = []): HiddenFactory
    {
        return HiddenFactory::new($parameters);
    }
}
