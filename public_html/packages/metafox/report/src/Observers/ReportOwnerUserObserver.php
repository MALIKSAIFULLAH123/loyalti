<?php

namespace MetaFox\Report\Observers;

use MetaFox\Report\Models\ReportOwnerUser;

/**
 * Class ReportOwnerUserObserver.
 */
class ReportOwnerUserObserver
{
    public function created(ReportOwnerUser $reportOwnerUser): void
    {
        $reportOwnerUser->report->incrementAmount('total_report');
    }
}

// end stub
