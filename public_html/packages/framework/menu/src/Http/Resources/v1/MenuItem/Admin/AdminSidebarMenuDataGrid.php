<?php

namespace MetaFox\Menu\Http\Resources\v1\MenuItem\Admin;

class AdminSidebarMenuDataGrid extends DataGrid
{
    protected function enableOrder(): bool
    {
        return false;
    }

    protected function setApiParams(): array
    {
        return [
            'q'          => ':q',
            'menu'       => 'core.adminSidebarMenu',
            'package_id' => ':package_id',
            'resolution' => ':resolution',
        ];
    }
}
