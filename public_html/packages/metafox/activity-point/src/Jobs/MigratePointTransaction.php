<?php

namespace MetaFox\ActivityPoint\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\ActivityPoint\Support\Facade\ActionType;
use MetaFox\Platform\Jobs\AbstractJob;

class MigratePointTransaction extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected string $packageId)
    {
        parent::__construct();
    }

    public function uniqueId(): string
    {
        return $this->packageId;
    }

    public function handle(): void
    {
        ActionType::setupDefaultActionTypes($this->packageId);
        ActionType::setupCustomActionTypes($this->packageId);
        ActionType::setupActionTypesInterpolateFromTransaction($this->packageId);
        ActionType::migrateTransactionExistPointSetting($this->packageId);
        ActionType::migrateTransactionNotExistPointSetting($this->packageId);
    }
}
