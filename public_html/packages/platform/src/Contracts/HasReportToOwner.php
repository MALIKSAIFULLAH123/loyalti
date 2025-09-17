<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface HasReportToOwner.
 */
interface HasReportToOwner
{
    public function canReportToOwner(User $user, Content $content = null): bool;

    public function canReportItem(User $user, Content $content = null): bool;
}
