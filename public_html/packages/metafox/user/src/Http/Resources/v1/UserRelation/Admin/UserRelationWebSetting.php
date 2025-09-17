<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserRelation\Admin;

use MetaFox\Platform\Resource\WebSetting;

/**
 *--------------------------------------------------------------------------
 * UserRelation Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class UserRelationWebSetting
 * Inject this class into property $resources.
 * @link \MetaFox\User\Http\Resources\v1\WebAppSetting::$resources;
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class UserRelationWebSetting extends WebSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('admincp/user/relation');

        $this->add('addItem')
            ->apiUrl('admincp/user/relation');

        $this->add('deleteItem')
            ->apiUrl('admincp/user/relation/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('user::phrase.delete_confirm_relation'),
                ]
            );

        $this->add('editItem')
            ->apiUrl('admincp/user/relation/:id');
    }
}
