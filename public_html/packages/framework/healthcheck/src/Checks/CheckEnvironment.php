<?php

namespace MetaFox\HealthCheck\Checks;

use MetaFox\Platform\HealthCheck\Checker;
use MetaFox\Platform\HealthCheck\Result;
use MetaFox\Platform\MetaFox;

class CheckEnvironment extends Checker
{
    public function check(): Result
    {
        $result = $this->makeResult();

        if (config('app.debug', false)) {
            $result->error(__p('health-check::phrase.the_debug_mode_was_expected_to_be_false'));
        }

        if ('production' !== config('app.env')) {
            $result->error(__p('health-check::phrase.the_app_env_was_expected_to_be_production', ['value' => config('app.env')]));
        }

        if (!app()->configurationIsCached() ||
            !app()->routesAreCached() ||
            !app()->eventsAreCached()
        ) {
            $result->error(__p('health-check::phrase.the_application_is_not_running_in_optimization_mode'));
        }

        if (!config('app.mfox_license_id') || !config('app.mfox_license_key')) {
            $result->error(__p('health-check::phrase.missing_metafox_license'));
        }

        if (file_exists(base_path('storage/framework/down'))) {
            $result->error(__p('health-check::phrase.maintenance_mode_error', ['value' => config('app.name')]));
        }

        if (!file_exists(storage_path('oauth-private.key'))
            || !file_exists(storage_path('oauth-public.key'))
        ) {
            $result->error(__p('health-check::phrase.missing_metafox_passport_key'));
        }

        if (ini_get('opcache.enabled') && config('app.mfox_preload_enabled')) {
            $expectedReload = base_path('preload.php');
            if ($expectedReload != ini_get('opcache.preload')) {
                $result->error(__p('health-check::phrase.missing_php_configuration', ['value' => $expectedReload]));
            }
        }

        $result->debug(__p('health-check::phrase.operating_system', ['value' => php_uname()]));
        $result->debug(__p('health-check::phrase.zend_engine_version', ['value' => zend_version()]));
        $result->debug(__p('health-check::phrase.platform_version', ['value' => MetaFox::getVersion()]));
        $result->debug(__p('health-check::phrase.php_version', ['value' => phpversion()]));
        $result->debug(__p('health-check::phrase.max_execution_time', ['value' => ini_get('max_execution_time')]));
        $result->debug(__p('health-check::phrase.memory_limit', ['value' => ini_get('memory_limit')]));

        return $result;
    }

    public function getName()
    {
        return __p('health-check::phrase.environment_variables');
    }
}
