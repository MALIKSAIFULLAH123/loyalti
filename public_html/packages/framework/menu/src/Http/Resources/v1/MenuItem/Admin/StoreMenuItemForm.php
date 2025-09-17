<?php

namespace MetaFox\Menu\Http\Resources\v1\MenuItem\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Menu\Models\Menu;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Menu\Support\Facades\MenuItem as MenuItemFacades;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * Class StoreMenuItemForm.
 */
class StoreMenuItemForm extends AbstractForm
{
    /**
     * @property Menu|MenuItem|null
     */
    private mixed $parentMenu = null;

    /**
     * @param mixed $resource
     */
    public function __construct(mixed $parentMenu = null)
    {
        parent::__construct();

        $this->parentMenu = $parentMenu;
    }

    protected function prepare(): void
    {
        $parentMenu = $this->getParentMenu();
        $values     = [
            'is_active'  => 0,
            'is_custom'  => 1,
            'resolution' => $parentMenu?->resolution ?? 'web',
        ];

        if ($parentMenu instanceof Menu) {
            $values['menu']       = $parentMenu->name;
            $values['resolution'] = $parentMenu->resolution;
        }

        if ($parentMenu instanceof MenuItem) {
            $values['menu']        = $parentMenu->menu;
            $values['parent_name'] = $parentMenu->name;
        }

        $this->title(__p('menu::phrase.add_new_item'))
            ->action(apiUrl('admin.menu.item.store'))
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::selectPackageAlias('module_id')
                ->required()
                ->label(__p('core::phrase.package_name'))
                ->description(__p('menu::phrase.menu_item_module_id_desc'))
                ->yup(Yup::string()->required()),
            Builder::text('label')
                ->required()
                ->label(__p('core::phrase.label'))
                ->description(__p('menu::phrase.menu_item_label_desc'))
                ->maxLength(255)
                ->yup(Yup::string()->required()),
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
            Builder::hidden('menu'),
            Builder::hidden('resolution'),
            Builder::hidden('parent_name'),
            Builder::hidden('is_custom'),
        );

        if (config('app.env') === 'local') {
            $basic->addFields(
                Builder::text('value')
                    ->required(false)
                    ->label(__p('core::phrase.value_label'))
                    ->maxLength(255),
                Builder::textArea('showWhen')
                    ->optional()
                    ->label(__p('menu::phrase.show_when_label'))
                    ->rows(2)
                    ->description(__p('menu::phrase.json_configuration_desc')),
                Builder::textArea('enableWhen')
                    ->optional()
                    ->label(__p('menu::phrase.enable_when_label'))
                    ->rows(2)
                    ->description(__p('menu::phrase.json_configuration_desc')),
            );
        }
        $basic->addFields(
            Builder::checkbox('is_active')
                ->label(__p('core::phrase.is_active')),
        );

        $this->addDefaultFooter();
    }

    /**
     * @return Menu|MenuItem|null
     */
    public function getParentMenu(): mixed
    {
        return $this->parentMenu;
    }
}
