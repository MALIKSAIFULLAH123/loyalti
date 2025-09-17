<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Menu\Http\Resources\v1\MenuItem\Admin;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * MenuItem Web Resource Setting
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
        $this->add('activeItem')
            ->apiUrl('admincp/menu/item/active/:id');

        $this->add('addItem')
            ->apiUrl('admincp/menu/item/form');

        $this->add('editItem')
            ->apiUrl('admincp/menu/item/form/:id');

        $this->add('deleteItem')
            ->apiUrl('admincp/menu/item/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p(
                        'core::phrase.delete_confirm_menu_item',
                        [],
                        null,
                        __p('core::phrase.delete_confirm')
                    ),
                ]
            );

        $this->add('searchForm')
            ->apiUrl('admincp/core/form/menu.item.search');
    }
}
