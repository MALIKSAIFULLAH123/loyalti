<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Story\Http\Resources\v1\Story;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('addItem')
            ->asPost()
            ->apiUrl('story');

        $this->add('viewItem')
            ->asGet()
            ->apiUrl('story/:id');

        $this->add('deleteItem')
            ->asDelete()
            ->apiUrl('story/:id')
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('story::phrase.delete_confirm'),
            ]);

        $this->add('viewAll')
            ->asGet()
            ->apiUrl('user_story')
            ->apiParams([
                'related_user_id' => ':related_user_id',
                'user_id'         => ':user_id',
            ]);

        $this->add('viewArchives')
            ->asGet()
            ->apiUrl('story-archive')
            ->apiParams([
                'user_id' => ':id',
            ]);

        $this->add('archive')
            ->asPost()
            ->apiUrl('story-archive');
    }
}
