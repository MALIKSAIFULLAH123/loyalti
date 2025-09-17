<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserPromotion\Admin;

use MetaFox\Platform\Resource\WebSetting;

/**
 *--------------------------------------------------------------------------
 * UserPromotion Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class UserPromotionWebSetting
 * Inject this class into property $resources.
 * @link \MetaFox\User\Http\Resources\v1\WebAppSetting::$resources;
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class UserPromotionWebSetting extends WebSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('admincp/user/promotion');

        $this->add('editItem')
            ->apiUrl('admincp/user/promotion/:id');

        $this->add('activeItem')
            ->apiUrl('admincp/user/promotion/active/:id');

        $this->add('deleteItem')
            ->apiUrl('admincp/user/promotion/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('core::phrase.delete_confirm'),
                ]
            );
    }
}
