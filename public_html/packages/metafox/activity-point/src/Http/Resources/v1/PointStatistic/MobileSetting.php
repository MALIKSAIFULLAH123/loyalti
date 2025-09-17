<?php
namespace MetaFox\ActivityPoint\Http\Resources\v1\PointStatistic;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewItem')
            ->apiUrl('activitypoint/statistic/:id');
    }
}
