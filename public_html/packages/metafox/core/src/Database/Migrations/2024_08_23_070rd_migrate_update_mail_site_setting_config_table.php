<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Core\Models\SiteSetting;

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('core_site_settings')) {
            return;
        }

        SiteSetting::withoutEvents(function () {
            SiteSetting::query()
                ->where('module_id', 'core')
                ->whereNull('config_name')
                ->whereIn('name', [
                    'core.services.ses',
                    'core.services.mailgun',
                    'core.services.postmark',
                ])
                ->get()
                ->collect()
                ->each(function ($setting) {
                    if (!$setting instanceof SiteSetting) {
                        return true;
                    }

                    $setting->updateQuietly(['config_name' => str_replace('core.', '', $setting->name)]);

                    return true;
                });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
