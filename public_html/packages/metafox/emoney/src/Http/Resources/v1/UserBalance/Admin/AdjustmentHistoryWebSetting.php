<?php
namespace MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

class AdjustmentHistoryWebSetting extends ResourceSetting
{
    protected function initialize():void
    {
        $this->add('searchForm')
            ->apiUrl('admincp/core/form/ewallet.adjustment_history.search');
    }
}
