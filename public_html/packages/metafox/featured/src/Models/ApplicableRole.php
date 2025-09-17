<?php

namespace MetaFox\Featured\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Authorization\Models\Role;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Featured\Database\Factories\ApplicableRoleFactory;

/**
 * stub: /packages/models/model.stub
 */

/**
 * Class ApplicableRole
 *
 * @property int $id
 * @property int $package_id
 * @property int $role_id
 * @method static ApplicableRoleFactory factory(...$parameters)
 */
class ApplicableRole extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'featured_applicable_user_role';

    protected $table = 'featured_applicable_roles';

    /** @var string[] */
    protected $fillable = [
        'package_id',
        'role_id',
    ];

    public $timestamps = false;

    /**
     * @return ApplicableRoleFactory
     */
    protected static function newFactory()
    {
        return ApplicableRoleFactory::new();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}

// end
