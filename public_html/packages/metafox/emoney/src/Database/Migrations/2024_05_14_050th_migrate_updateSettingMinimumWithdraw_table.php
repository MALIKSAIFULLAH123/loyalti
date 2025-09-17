<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Platform\Facades\Settings;

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
        $setting = (float) Settings::get('ewallet.minimum_withdraw', 0);
        if (!is_numeric($setting)) {
            return;
        }
        
        Settings::save([
            'ewallet.minimum_withdraw' => ['USD' => $setting],
        ]);

        // to do here
    }

};
