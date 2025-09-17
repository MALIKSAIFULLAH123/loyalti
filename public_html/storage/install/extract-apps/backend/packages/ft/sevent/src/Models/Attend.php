<?php

namespace Foxexpert\Sevent\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

class Attend extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'sevent_attend';

    protected $table = 'sevent_attends';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'sevent_id',
        'type_id'  // 1 attending 2 maybe
    ];
}