<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Http\Resources\v1\Category\Admin;

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
            ->apiUrl('admincp/video/category');

        $this->add('deleteItem')
            ->apiUrl('admincp/video/category/form/delete/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('video::phrase.delete_confirm_category'),
                ]
            );

        $this->add('addItem')
            ->apiUrl('admincp/video/category/form');

        $this->add('editItem')
            ->apiUrl('admincp/video/category/form/:id');

        $this->add('activeItem')
            ->apiUrl('admincp/video/category/active/:id');
    }
}
