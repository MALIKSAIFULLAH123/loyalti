<?php
namespace MetaFox\Forum\Http\Requests\v1\Forum\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Forum\Repositories\UserRolePermissionRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;

class SetupPermissionRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'role_id' => ['required', 'numeric', 'exists:auth_roles,id'],
        ];

        $permissions = resolve(UserRolePermissionRepositoryInterface::class)->getPermissionOptions();
        $roles = resolve(RoleRepositoryInterface::class)->getRoleOptionsWithout([1]);

        foreach ($permissions as $permission) {
            foreach ($roles as $role) {
                $rules[$permission['name'].'__'.$role['value']] = ['sometimes', 'boolean'];
            }
        }

        return $rules;
    }
}
