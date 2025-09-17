<?php

namespace MetaFox\Menu\Http\Resources\v1\Menu\Admin;

use MetaFox\Menu\Models\Menu as Model;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * UpdateMenuForm
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateMenuForm.
 * @property Model $resource
 * @ignore
 * @mixin Model
 */
class UpdateMenuForm extends StoreMenuForm
{
    public function boot(?int $id = null)
    {
        $this->resource = resolve(MenuRepositoryInterface::class)->find($id);
    }

    protected function prepare(): void
    {
        $model = $this->resource;

        $this->asPut()
            ->title(__p('menu::phrase.edit_menu'))
            ->action(apiUrl('admin.menu.menu.update', ['menu' => $model->id]))
            ->setValue([
                'resource_name' => $model->resource_name,
                'name'          => $model->name,
                'module_id'     => $model->module_id,
                'is_active'     => $model->is_active,
                'is_mobile'     => $model->is_mobile,
                'is_admin'      => $model->is_admin,
            ]);
    }
}
