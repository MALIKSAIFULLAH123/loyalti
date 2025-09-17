<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Platform\MetaFoxConstant;
use Illuminate\Support\Facades\Schema;

/*
 * stub: /packages/database/migration.stub
 */

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

        /**
         * @var SiteSetting $setting
         */
        $setting = SiteSetting::query()
            ->where('name', '=', 'user.on_signup_new_friend')
            ->first();

        if (null === $setting) {
            return;
        }

        if (MetaFoxConstant::EMPTY_STRING === $setting->value_actual || null === $setting->value_actual || !is_numeric($setting->value_actual)) {
            $setting->update(['value_actual' => []]);
            return;
        }

        if (is_array($setting->value_actual)) {
            return;
        }

        $setting->update(['value_actual' => [(int)$setting->value_actual]]);
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
