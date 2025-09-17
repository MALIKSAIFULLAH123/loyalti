<?php

namespace MetaFox\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class PrivacyStream.
 * @property int    $stream_id
 * @property int    $privacy_id
 * @property int    $item_id
 * @property string $item_type
 * @mixin Builder
 */
class PrivacyStream extends Model implements Entity
{
    use HasEntity;
    public const ENTITY_TYPE = 'core_privacy_stream';

    protected $table = 'core_privacy_streams';

    public $timestamps = false;

    protected $fillable = [
        'privacy_id',
        'item_id',
        'item_type',
    ];
}
