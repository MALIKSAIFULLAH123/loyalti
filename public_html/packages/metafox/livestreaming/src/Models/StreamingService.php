<?php

namespace MetaFox\LiveStreaming\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\LiveStreaming\Database\Factories\StreamingServiceFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class StreamingService.
 *
 * @property        int                       $id
 * @property        string                    $driver
 * @property        string                    $name
 * @property        int                       $is_default
 * @property        int                       $is_active
 * @property        string                    $service_class
 * @property        array<string, mixed>|null $extra
 * @property        string                    $detail_link
 * @method   static StreamingServiceFactory   factory(...$parameters)
 */
class StreamingService extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'livestreaming_service';

    protected $table = 'livestreaming_service';

    /** @var string[] */
    protected $fillable = [
        'is_default',
        'is_active',
        'driver',
        'name',
        'service_class',
        'extra',
        'created_at',
        'updated_at',
    ];

    /** @var array<string, mixed> */
    protected $casts = [
        'extra' => 'array',
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        'detail_link',
    ];

    /**
     * @return StreamingServiceFactory
     */
    protected static function newFactory()
    {
        return StreamingServiceFactory::new();
    }

    public function getDetailLinkAttribute(): string
    {
        $default = '/livestreaming/setting/' . $this->driver;

        $url =  Arr::get($this->extra, 'url', $default);

        return str_starts_with($url, '/admincp')? str_replace('/admincp', '', $url): $url;
    }
}

// end
