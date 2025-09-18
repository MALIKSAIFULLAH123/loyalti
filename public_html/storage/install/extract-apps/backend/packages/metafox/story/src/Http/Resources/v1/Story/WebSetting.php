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
class WebSetting extends ResourceSetting
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

        $this->add('addPhotoStory')
            ->asGet()
            ->apiUrl('core/form/story.photo.store');

        $this->add('addTextStory')
            ->asGet()
            ->apiUrl('core/form/story.text.store');

        $this->add('addPhotoStoryMobile')
            ->asGet()
            ->apiUrl('core/form/story.photo.mobile.store');

        $this->add('addTextStoryMobile')
            ->asGet()
            ->apiUrl('core/form/story.text.mobile.store');

        $this->add('viewAll')
            ->asGet()
            ->apiUrl('user_story')
            ->apiRules([
                'related_user_id' => ['truthy', 'related_user_id'],
                'user_id'         => ['truthy', 'user_id'],
            ])
            ->apiParams([
                'related_user_id' => ':related_user_id',
                'user_id'         => ':user_id',
            ]);

        $this->add('viewArchives')
            ->asGet()
            ->apiUrl('story-archive')
            ->apiRules([
                'user_id'   => ['truthy', 'user_id'],
                'story_id'  => ['truthy', 'story_id'],
                'from_date' => ['truthy', 'from_date'],
                'to_date'   => ['truthy', 'to_date'],
            ])
            ->apiParams([
                'user_id'   => ':id',
                'story_id'  => ':story_id',
                'from_date' => ':from_date',
                'to_date'   => ':to_date',
            ]);

        $this->add('archive')
            ->asPost()
            ->apiUrl('story-archive');

        if (app_active('metafox/friend')) {
            $this->add('getFriendSuggest')
                ->apiUrl('friend/suggestion')
                ->apiParams([
                    'view' => 'available_suggestion'
                ]);
        }
    }
}
