<?php

namespace MetaFox\Authorization\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFox\Authorization\Database\Factories\RoleFactory;
use MetaFox\Authorization\Models\Pivot\RoleValuePermission;
use MetaFox\Authorization\Traits\RoleHasValuePermissionTrait;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\PlatformRole;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Contracts\RoleHasValuePermission;

/**
 * Class Role.
 *
 * @mixin Builder
 *
 * @property int        $id
 * @property string     $name
 * @property string     $guard_name
 * @property int        $is_special
 * @property int        $is_custom
 * @property int        $users_count
 * @property Collection $permissions
 * @property string     $created_at
 * @property string     $updated_at
 * @property string     $permission_link
 * @property int        $parent_id
 * @property int        $root_parent_id
 * @property Role       $parentRole
 * @property string     $browse_user_link
 *
 * @method static RoleFactory factory(...$parameters)
 */
class Role extends \Spatie\Permission\Models\Role implements
    Entity,
    PlatformRole,
    RoleHasValuePermission,
    HasTitle
{
    use HasEntity;
    use HasFactory;
    use RoleHasValuePermissionTrait;

    public const DEFAULT_GUARD = 'api';

    public const ENTITY_TYPE = 'user_role';

    protected $table = 'auth_roles';

    /**
     * @var string[]
     */
    protected $appends = ['is_custom', 'permission_link'];

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    public function valuePermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('permission.table_names.role_has_value_permissions'),
            'role_id',
            'permission_id'
        )->using(RoleValuePermission::class)
            ->withPivot('value');
    }

    public function getIsCustomAttribute(): bool
    {
        return !$this->is_special;
    }

    public function getPermissionLinkAttribute(): string
    {
        return url_utility()->makeApiUrl('/authorization/permission?module_id=user&role_id=' . $this->entityId());
    }

    public function toTitle(): string
    {
        return $this->name;
    }

    public function parentRole(): BelongsTo
    {
        return $this->belongsTo($this, 'parent_id', 'id');
    }

    public function subRoles(): HasMany
    {
        $relation = $this->hasMany(self::class, 'parent_id', 'id');
        $relation->getQuery()->whereNot('id', $this->id);

        return $relation;
    }

    public function getTotalInheritedAttribute(): int
    {
        return $this->subRoles()->count();
    }

    public function getBrowseUserLinkAttribute(): string
    {
        return url_utility()->makeApiUrl('/user/user/browse?group=' . $this->entityId());
    }
}
