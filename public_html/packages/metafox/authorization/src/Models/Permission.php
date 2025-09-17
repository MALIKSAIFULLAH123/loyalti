<?php

namespace MetaFox\Authorization\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Authorization\Database\Factories\PermissionFactory;
use MetaFox\Authorization\Models\Pivot\RoleValuePermission;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\User\Contracts\PlatformPermission;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;

/**
 * Class Permission.
 * @mixin Builder
 * @property        int                 $id
 * @property        string              $name
 * @property        string              $transformed_name
 * @property        string              $guard_name
 * @property        string              $description
 * @property        string              $module_id
 * @property        string              $entity_type
 * @property        string              $action
 * @property        array               $extra
 * @property        string              $data_type
 * @property        int                 $is_public
 * @property        bool                $require_admin
 * @property        bool                $require_staff
 * @property        mixed               $default_value
 * @property        bool                $require_user
 * @property        string              $created_at
 * @property        string              $updated_at
 * @property        RoleValuePermission $pivot
 * @property        Collection|Role[]   $roles
 * @property        Collection|Role[]   $rolesHasValuePermissions
 * @method   static PermissionFactory   factory()
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class Permission extends \Spatie\Permission\Models\Permission implements Entity, PlatformPermission
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'user_permission';

    /**
     * @var string
     */
    protected $table = 'auth_permissions';

    /**
     * @var string[]
     */
    protected $fillable = [
        'id', 'name', 'guard_name', 'module_id', 'default_value', 'package_id', 'entity_type', 'action', 'extra',
        'data_type', 'is_public', 'is_editable',
        'require_admin', 'require_staff', 'require_user',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'extra'         => 'array',
        'is_public'     => 'boolean',
        'require_admin' => 'boolean',
        'require_staff' => 'boolean',
        'require_user'  => 'boolean',
    ];

    protected static function newFactory(): PermissionFactory
    {
        return PermissionFactory::new();
    }

    public function rolesHasValuePermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_value_permissions'),
            'permission_id',
            'role_id'
        )->using(RoleValuePermission::class)
            ->withPivot('value');
    }

    public function rolesHasPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_permissions'),
            'permission_id',
            'role_id',
        );
    }

    public function assignRoleWithPivot(array $params): self
    {
        $roles = array_keys($params);

        /**
         * @var Role[] $roles
         */
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (empty($role)) {
                    return false;
                }

                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->each(function ($role) {
                if (!$role instanceof Role) {
                    throw GuardDoesNotMatch::create(Role::DEFAULT_GUARD, $this->getGuardNames());
                }

                $this->ensureModelSharesGuard($role);
            })
            ->all();

        $model = $this->getModel();

        $updatedData = [];

        foreach ($roles as $role) {
            $key = null;

            if (Arr::has($params, $role->name)) {
                $key = $role->name;
            }

            if (Arr::has($params, $role->entityId())) {
                $key = $role->entityId();
            }

            if (null === $key) {
                continue;
            }

            $updatedData[$role->entityId()] = [
                'value' => $params[$key],
            ];
        }

        if ($model->exists) {
            $this->rolesHasValuePermissions()->sync($updatedData, false);
            $model->load('rolesHasValuePermissions');

            $this->forgetCachedPermissions();

            return $this;
        }

        $class = get_class($model);

        $class::saved(
            function (self $object) use ($updatedData, $model) {
                static $modelLastFiredOn;
                if ($modelLastFiredOn !== null && $modelLastFiredOn === $model) {
                    return;
                }
                $object->rolesHasValuePermissions()->sync($updatedData, false);
                $object->load('rolesHasValuePermissions');
                $modelLastFiredOn = $object;
            }
        );

        $this->forgetCachedPermissions();

        return $this;
    }

    public static function findByName(string $name, $guardName = 'api'): PermissionContract
    {
        $permission = static::getPermission(['name' => $name, 'guard_name' => $guardName]);

        if (!$permission) {
            abort(400, "There is no permission named `{$name}` for guard `{$guardName}`.");
        }

        return $permission;
    }

    /**
     * @param string $name
     * @param mixed  $guardName
     *
     * @return PermissionContract
     */
    public static function findByWildcardName(string $name, $guardName = 'api'): PermissionContract
    {
        $wcName = preg_replace('/^\w+/', '*', $name, 1);
        if (empty($wcName)) {
            abort(400, "Could not convert the permission named {$name} to wildcard permission.");
        }

        return self::findByName($wcName, $guardName);
    }

    /**
     * Get form label.
     *
     * @return string
     */
    public function getLabelPhrase(): string
    {
        return Str::snake(sprintf(
            '%s::permission.can_%s_%s_label',
            $this->module_id,
            $this->action,
            $this->entity_type
        ));
    }

    /**
     * Get form label.
     *
     * @return string
     */
    public function getHelpPhrase(): string
    {
        return Str::snake(sprintf(
            '%s::permission.can_%s_%s_desc',
            $this->module_id,
            $this->action,
            $this->entity_type
        ));
    }

    public function getTransformedNameAttribute(): string
    {
        return Str::replace('.', MetaFoxConstant::NESTED_ARRAY_SEPARATOR, $this->name);
    }
}
