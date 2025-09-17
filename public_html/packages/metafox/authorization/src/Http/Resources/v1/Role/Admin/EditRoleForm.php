<?php

namespace MetaFox\Authorization\Http\Resources\v1\Role\Admin;

use MetaFox\Authorization\Models\Role as Model;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditRoleForm.
 * @property Model $resource
 *
 * @driverType form
 * @driverName user.user_role.update
 */
class EditRoleForm extends CreateRoleForm
{
    public function boot(RoleRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('/admincp/authorization/role/' . $this->resource->entityId())
            ->asPatch()
            ->setValue([
                'name' => $this->resource->name,
            ]);
    }
}
