<?php

namespace MetaFox\Event\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class MassEmail.
 *
 * @property int    $id
 * @property int    $event_id
 * @property int    $user_id
 * @property string $user_type
 * @property string $created_at
 * @property string $updated_at
 */
class MassEmail extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'mass_email';

    protected $table = 'event_mass_emails';

    /** @var string[] */
    protected $fillable = [
        'event_id',
        'user_id',
        'user_type',
    ];
}

// end
