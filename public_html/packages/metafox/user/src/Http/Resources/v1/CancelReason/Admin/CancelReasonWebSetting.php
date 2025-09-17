<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\CancelReason\Admin;

use MetaFox\Platform\Resource\WebSetting;

/**
 *--------------------------------------------------------------------------
 * CancelReason Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class CancelReasonWebSetting
 * Inject this class into property $resources.
 * @link \MetaFox\User\Http\Resources\v1\WebAppSetting::$resources;
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CancelReasonWebSetting extends WebSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('admincp/user/cancel/reason');

        $this->add('editItem')
            ->apiUrl('admincp/user/cancel/reason/:id');

        $this->add('deleteItem')
            ->apiUrl('admincp/user/cancel/reason/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('user::phrase.delete_confirm_cancel_reason'),
                ]
            );

        $this->add('addItem')
            ->apiUrl('admincp/user/cancel/reason');
    }
}
