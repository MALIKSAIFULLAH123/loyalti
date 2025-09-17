<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Localize\Http\Resources\v1\Language\Admin;

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
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('admincp/language');

        $this->add('deleteItem')
            ->apiUrl('admincp/language/:id');

        $this->add('addItem')
            ->apiUrl('admincp/language/form');

        $this->add('editItem')
            ->apiUrl('admincp/language/form/:id');

        $this->add('exportItem')
            ->apiUrl('admincp/language/export-form/:id');

        $this->add('importItem')
            ->apiUrl('admincp/language/import-form');
    }
}
