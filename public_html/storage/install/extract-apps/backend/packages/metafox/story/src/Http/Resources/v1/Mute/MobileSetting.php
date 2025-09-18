<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Story\Http\Resources\v1\Mute;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->asGet()
            ->apiUrl(apiUrl('story-mute.index'));

        $this->add('mute')
            ->apiUrl('core/mobile/form/story.mute.store');

        $this->add('unmute')
            ->apiUrl(apiUrl('story-mute.unmute'))
            ->asPatch()
            ->apiParams(['user_id' => ':user_id']);

        $this->add('deleteItem')
            ->asDelete()
            ->apiUrl(apiUrl('story-mute.destroy', ['story_mute' => ':id']));
    }

}
