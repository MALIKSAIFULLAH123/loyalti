<?php

namespace MetaFox\Quiz\Models;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

class PlayedResult extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'quiz_played_result';

    protected $table = 'quiz_played_results';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
        'user_id',
    ];
}
