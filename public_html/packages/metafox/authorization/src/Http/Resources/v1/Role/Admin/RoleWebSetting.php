<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Authorization\Http\Resources\v1\Role\Admin;

use MetaFox\Platform\Resource\WebSetting;

/**
 *--------------------------------------------------------------------------
 * Role Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class RoleWebSetting
 * Inject this class into property $resources.
 * @link \MetaFox\Authorization\Http\Resources\v1\WebAppSetting::$resources;
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class RoleWebSetting extends WebSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('admincp/authorization/role');

        $this->add('addItem')
            ->apiUrl('admincp/authorization/role/form');

        $this->add('editItem')
            ->apiUrl('admincp/authorization/role/form/:id');

        $this->add('deleteItem')
            ->apiUrl('admincp/authorization/role/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('user::phrase.delete_confirm_role'),
                ]
            );
    }
}
