<?php

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Models\PointPackage;

class IapGetProductTypeListener
{
    public function handle()
    {
        return [
            'package_id' => 'metafox/activity-point',
            'value'      => PointPackage::ENTITY_TYPE,
            'url'        => '/activitypoint/package/browse',
            'label'      => __p('activitypoint::phrase.activitypoint_package'),
        ];
    }
}
