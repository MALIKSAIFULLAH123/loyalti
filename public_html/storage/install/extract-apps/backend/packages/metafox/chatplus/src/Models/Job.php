<?php

namespace MetaFox\ChatPlus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\ChatPlus\Database\Factories\JobFactory;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Job.
 *
 * @property        int        $id
 * @property        string     $name
 * @property        int        $is_sent
 * @property        array      $data
 * @method   static JobFactory factory(...$parameters)
 */
class Job extends Model
{
    use HasEntity;
    use HasFactory;

    protected $table = 'chatplus_jobs';

    /** @var string[] */
    protected $fillable = [
        'is_sent',
        'name',
        'data',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'job'  => 'array',
        'data' => 'array',
    ];

    protected static function newFactory(): JobFactory
    {
        return JobFactory::new();
    }
}

// end
