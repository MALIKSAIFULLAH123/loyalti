<?php

namespace MetaFox\Poll\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Poll\Database\Factories\DesignFactory;

/**
 * class Design.
 *
 * @property int    $id
 * @property string $percentage
 * @property string $background
 * @property string $border
 * @method   static DesignFactory factory()
 */
class Design extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'poll_design';

    protected $table = 'poll_designs';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'percentage',
        'background',
        'border',
    ];

    /**
     * @param  array<string, mixed> $parameters
     * @return DesignFactory
     */
    public static function newFactory(array $parameters = []): DesignFactory
    {
        return DesignFactory::new($parameters);
    }
}
