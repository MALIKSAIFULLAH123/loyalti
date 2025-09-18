<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Block;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('group-block')
            ->apiParams(['group_id' => ':id']);

        $this->add('unblockFromGroup')
            ->apiUrl('group-unblock')
            ->asDelete()
            ->apiParams(['group_id' => ':group_id', 'user_id' => ':user_id'])
            ->confirm([
                'title'        => __p('group::phrase.confirm_unblock_form_group_title'),
                'message'      => 'confirm_unblock_form_group_desc',
                'phraseParams' => [
                    'userName' => ':user.full_name',
                ],
            ]);
    }
}
