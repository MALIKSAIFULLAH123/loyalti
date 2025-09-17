<?php

namespace MetaFox\Menu\Http\Resources\v1\MenuItem\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use Illuminate\Support\Arr;
use MetaFox\Menu\Models\Menu;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'menu';
    protected string $resourceName = 'item';

    /**
     * @property Menu|MenuItem|null
     */
    private mixed $parentMenu = null;
    public ?int   $parentId   = null;

    protected array $apiRules = [
        'q'          => ['truthy', 'q'],
        'menu'       => ['truthy', 'menu'],
        'package_id' => ['truthy', 'package_id'],
        'resolution' => ['truthy', 'resolution'],
    ];

    protected array $apiParams = [
        'q'          => ':q',
        'menu'       => ':menu',
        'package_id' => ':package_id',
        'resolution' => ':resolution',
    ];

    protected function initialize(): void
    {
        $this->sortable();

        $this->setDataSource(apiUrl('admin.menu.item.index'), $this->apiParams, $this->apiRules);

        $this->addColumn('icon')
            ->header(__p('app::phrase.icon'))
            ->asIcon();

        $this->addColumn('label')
            ->header(__p('core::phrase.label'))
            ->linkTo('url')
            ->flex(2);

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('module_id')
            ->header(__p('core::phrase.package_name'))
            ->width(200);

        $this->addColumn('is_active')
            ->header(__p('app::phrase.is_active'))
            ->asToggleActive();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy', 'delete', 'toggleActive']);

            $actions->add('orderItem')
                ->asPost()
                ->apiUrl('admincp/menu/menu-item/order');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete(null, null, [
                'and',
                ['truthy', 'item.extra.can_delete'],
            ]);
        });
    }

    protected function enableOrder(): bool
    {
        return true;
    }

    public function boot(?int $parentId = null): void
    {
        if (!$parentId) {
            return;
        }

        $this->parentId = $parentId;
        $parentMenu     = $this->findParentMenu($parentId);
        $this->setParentMenu($parentMenu);

        if (!$parentMenu) {
            return;
        }

        $menuExtra = $parentMenu->extra;

        if (Arr::has($menuExtra, 'order_by')) {
            $this->sortable(false);
        }

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate(__p('menu::phrase.add_new_item'));
        });

        $this->withActions(function (Actions $actions) use ($parentMenu) {
            if ($parentMenu instanceof Menu) {
                $actions->add('addItem')
                    ->apiUrl(apiUrl('admin.menu.item.create', ['parentId' => $parentMenu->entityId()]));
            }

            if ($parentMenu instanceof MenuItem) {
                $actions->add('addItem')
                    ->apiUrl(apiUrl('admin.menu.item.child.create', ['parentId' => $parentMenu->entityId()]));
            }
        });
    }

    /**
     * @return Menu|MenuItem|null
     */
    public function getParentMenu(): mixed
    {
        return $this->parentMenu;
    }

    /**
     * @param Menu|MenuItem|null
     */
    public function setParentMenu(mixed $menu = null): void
    {
        $this->parentMenu = $menu;
    }

    /**
     * @param  ?int $parentId
     * @return Menu|MenuItem|null
     */
    protected function findParentMenu(?int $parentId = null): mixed
    {
        $repository = resolve(MenuRepositoryInterface::class);
        $parentMenu = $repository->getModel()->newModelQuery()->find($parentId);

        if (!$parentMenu instanceof Menu) {
            return null;
        }

        return $parentMenu;
    }
}
