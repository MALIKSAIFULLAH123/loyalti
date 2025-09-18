<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_admin_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('addItem')
            ->apiUrl('/admincp/subscription-cancel-reason/form');

        $this->add('editItem')
            ->apiUrl('/admincp/subscription-cancel-reason/form/:id');

        $this->add('deleteItem')
            ->apiUrl('/admincp/subscription-cancel-reason/:id')
            ->asDelete()
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('subscription::admin.are_you_sure_you_want_to_delete_this_reason_permanently'),
            ]);

        $this->add('activeItem')
            ->apiUrl('/admincp/subscription-cancel-reason/active/:id')
            ->apiParams([
                'is_active' => 1,
            ])
            ->asPatch();

        $this->add('deactiveItem')
            ->apiUrl('/admincp/subscription-cancel-reason/active/:id')
            ->apiParams([
                'is_active' => 0,
            ])
            ->asPatch();

        $this->add('getDeleteForm')
            ->apiUrl('/admincp/subscription-cancel-reason/delete/:id')
            ->asGet();
    }
}
