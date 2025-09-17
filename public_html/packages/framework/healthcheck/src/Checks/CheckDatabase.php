<?php

namespace MetaFox\HealthCheck\Checks;

use Illuminate\Support\Facades\DB;
use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;
use MetaFox\Platform\Support\DbTableHelper;

class CheckDatabase extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();

        try {
            $result->success(__p('health-check::phrase.database_connection', ['value' => DB::getDriverName()]));
            $version = DbTableHelper::getDriverVersion();
            $dbSize  = DbTableHelper::getDatabaseSize();

            $result->success(__p('health-check::phrase.database_driver_version', ['value' => $version]));
            $result->success(__p('health-check::phrase.database_size', ['value' => human_readable_bytes($dbSize)]));
        } catch (\Exception $exception) {
            $result->error(__p('health-check::phrase.could_not_connect_to_database', ['value' => $exception->getMessage()]));
        }

        return $result;
    }

    public function getName()
    {
        return __p('health-check::phrase.database');
    }
}
