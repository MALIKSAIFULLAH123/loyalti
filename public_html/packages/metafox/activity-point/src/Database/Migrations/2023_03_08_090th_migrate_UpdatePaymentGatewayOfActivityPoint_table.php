<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Payment\Models\Gateway;

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
        Gateway::query()
            ->where('service', 'activitypoint')
            ->update([
                'is_test' => false,
            ]);
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
