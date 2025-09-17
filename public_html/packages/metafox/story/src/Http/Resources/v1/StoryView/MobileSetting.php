<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Story\Http\Resources\v1\StoryView;

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
            ->apiUrl('story-view')
            ->apiParams([
                'story_id' => ':story_id',
            ]);

        $this->add('viewAll')
            ->asGet()
            ->apiUrl('story-view')
            ->apiParams([
                'story_id' => ':story_id',
            ]);
    }
}
