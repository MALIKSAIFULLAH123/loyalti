<?php

namespace MetaFox\Menu\Http\Resources\v1\MenuItem\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;
use MetaFox\Menu\Support\Facades\MenuItem as MenuItemFacades;
use MetaFox\Platform\MetaFoxConstant;

/**
 * --------------------------------------------------------------------------
 * UpdateMenuItemForm
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateMenuItemForm.
 * @property MenuItem $resource
 */
class UpdateMenuItemForm extends AbstractForm
{
    public function boot(?int $id = null): void
    {
        $this->resource = resolve(MenuItemRepositoryInterface::class)->find($id);
    }

    protected function prepare(): void
    {
        $showWhen   = Arr::get($this->resource->extra, 'showWhen');
        $enableWhen = Arr::get($this->resource->extra, 'enableWhen', '');
        $subInfo    = Arr::get($this->resource->extra, 'subInfo', '');

        $this
            ->title(__p('menu::phrase.edit_menu_item'))
            ->action(apiUrl('admin.menu.item.update', ['item' => $this->resource->entityId()]))
            ->asPut()
            ->setValue([
                'label'       => $this->resource->label_var,
                'menu'        => $this->resource->menu,
                'is_active'   => $this->resource->is_active,
                'module_id'   => $this->resource->module_id,
                'parent_name' => $this->resource->parent_name,
                'name'        => $this->resource->name,
                'icon'        => $this->resource->icon,
                'iconColor'   => $this->resource->icon_color,
                'as'          => $this->resource->as,
                'to'          => $this->resource->to,
                'value'       => $this->resource->value,
                'sub_info'    => $subInfo,
                'ordering'    => $this->resource->ordering,
                'testid'      => $this->resource->testid,
                'resolution'  => $this->resource->resolution,
                'is_custom'   => (int) $this->resource->is_custom,
                'showWhen'    => !empty($showWhen) ? json_encode($showWhen) : '',
                'enableWhen'  => !empty($enableWhen) ? json_encode($enableWhen) : '',
            ]);
    }
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('label')
                ->required()
                ->label(__p('core::phrase.label'))
                ->maxLength(255),
            Builder::text('to')
                ->required(false)
                ->label(__p('core::phrase.to_label'))
                ->maxLength(255),
            Builder::text('sub_info')
                ->label(__p('core::phrase.description'))
                ->maxLength(255),
            Builder::text('icon')
                ->component('IconPicker')
                ->required(false)
                ->label(__p('app::phrase.icon'))
                ->maxLength(255),
            Builder::colorPicker('iconColor')
                ->label(__p('app::phrase.icon_color'))
                ->showWhen(['eq', 'resolution', MetaFoxConstant::RESOLUTION_MOBILE]),
            Builder::checkbox('is_active')
                ->label(__p('core::phrase.is_active')),
            // Builder::textArea('showWhen')
            //     ->optional()
            //     ->label(__p('menu::phrase.show_when_label'))
            //     ->rows(2)
            //     ->description(__p('menu::phrase.json_configuration_desc')),
            // Builder::textArea('enableWhen')
            //     ->optional()
            //     ->label(__p('menu::phrase.enable_when_label'))
            //     ->rows(2)
            //     ->description(__p('menu::phrase.json_configuration_desc')),
            Builder::hidden('is_custom'),
        );

        $this->addDefaultFooter();
    }
}
