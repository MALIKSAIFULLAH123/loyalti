<?php

namespace MetaFox\Menu\Http\Resources\v1\MenuItem\Admin;

use Illuminate\Support\Arr;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class ChildrenDataGrid extends DataGrid
{
    /**
     * @param  ?int               $parentId
     * @return Menu|MenuItem|null
     */
    protected function findParentMenu(?int $parentId = null): mixed
    {
        $repository = resolve(MenuItemRepositoryInterface::class);
        $parentMenu = $repository->getModel()->newModelQuery()->find($parentId);

        if (!$parentMenu instanceof MenuItem) {
            return null;
        }

        return $parentMenu;
    }
}
