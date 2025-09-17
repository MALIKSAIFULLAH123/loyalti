<?php

use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        /** @var SiteSetting|null $setting */
        $setting = SiteSetting::query()
            ->where('name', '=', 'chatplus.jitsi_domain_option')
            ->first();
        if (!$setting || $setting->value_actual !== 'jitsi') {
            return;
        }
        $setting->value_actual = 'metafox';
        $setting->save();
        try {
            resolve(ChatServerInterface::class)->syncSettings(false, false);
        } catch (Exception $e) {
            // Silent error
        }
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
