<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Http\Resources\v1\ActivitySchedule;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('feed-schedule')
            ->asGet()
            ->apiParams([
                'entity_id'   => ':id',
                'entity_type' => ':appName',
            ]);
        $this->add('viewItem')
            ->apiUrl('feed-schedule/:id')
            ->asGet();
        $this->add('updateScheduled')
            ->apiUrl('feed-schedule/edit/:id')
            ->asGet();
        $this->add('sendNowScheduled')
            ->apiUrl('feed-schedule/send-now/:id')
            ->asPost();
        $this->add('updateItem')
            ->apiUrl('feed-schedule/:id')
            ->asPut();
        $this->add('deleteItem')
            ->apiUrl('feed-schedule/:id')
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('activity::phrase.delete_confirm'),
                ]
            );
    }
}
