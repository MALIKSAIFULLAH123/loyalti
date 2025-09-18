<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Category\Admin;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Language Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('admincp/group/category');

        $this->add('deleteItem')
            ->apiUrl('admincp/group/category/form/delete/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('group::phrase.delete_confirm_category'),
                ]
            );

        $this->add('addItem')
            ->apiUrl('admincp/group/category/form');

        $this->add('editItem')
            ->apiUrl('admincp/group/category/form/:id');

        $this->add('activeItem')
            ->apiUrl('admincp/group/category/active/:id');
    }
}
