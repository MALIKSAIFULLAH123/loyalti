<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MetaFox\User\Models\UserPreference;

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
        $channels = ["webpush", "mobilepush"];
        UserPreference::query()
            ->whereRaw("value = '[]'")
            ->where('name', 'subscribe_notification_channels')
            ->update([
                'value' => $channels,
            ]);

        foreach ($channels as $channel) {
            $value = ',"' . $channel . '"]';
            UserPreference::query()
                ->whereNot('value', database_driver() == 'pgsql' ? 'ilike' : 'like', "%$channel%")
                ->where('name', 'subscribe_notification_channels')
                ->update([
                    'value' => DB::raw("REPLACE(value, ']', '$value')"),
                ]);
        }

        // to do here
    }
};
