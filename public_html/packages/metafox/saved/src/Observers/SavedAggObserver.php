<?php

namespace MetaFox\Saved\Observers;

use Illuminate\Support\Facades\Artisan;
use MetaFox\Saved\Models\SavedAgg;

/**
 * Class SavedAggObserver.
 */
class SavedAggObserver
{
    public function created(SavedAgg $agg): void
    {
    }
}

// end stub
