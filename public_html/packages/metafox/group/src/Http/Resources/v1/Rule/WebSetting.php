<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Rule;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * GroupMember Web Resource Setting
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
        $this->add('addItem')
            ->apiUrl('group-rule/form')
            ->apiParams(['group_id' => ':id']);

        $this->add('editItem')
            ->apiUrl('group-rule/form/:id');

        $this->add('viewAll')
            ->apiUrl('group-rule')
            ->apiParams(['group_id' => ':id']);

        $this->add('deleteItem')
            ->apiUrl('group-rule/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p(
                        'group::phrase.delete_confirm_rule',
                        [],
                        null,
                        __p('core::phrase.delete_confirm')
                    ),
                ]
            );

        $this->add('orderItems')
            ->apiUrl('group-rule/order')
            ->asPut();
    }
}
