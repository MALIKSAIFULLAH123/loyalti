<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Menu\Http\Resources\v1\Menu\Admin;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Menu Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('deleteItem')
            ->apiUrl('admincp/menu/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('core::phrase.delete_confirm_menu'),
                ]
            );

        $this->add('activeItem')
            ->apiUrl('admincp/menu/active/:id');

        $this->add('addItem')
            ->apiUrl('admincp/menu/form');

        $this->add('editItem')
            ->apiUrl('admincp/menu/form/:id');

        $this->add('searchForm')
            ->apiUrl('admincp/core/form/menu.search');
    }
}
