<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
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
        $setting = Settings::get('user.send_welcome_email');
        if ($setting === null) {
            return;
        }

        Settings::destroy('user', ['user.send_welcome_email']);

        if ($setting) {
            return;
        }

        $type = \MetaFox\Notification\Models\Type::query()->where('type', 'user_welcome')->first();

        if (!$type instanceof \MetaFox\Notification\Models\Type) {
            return;
        }

        \MetaFox\Notification\Models\TypeChannel::query()
            ->where('type_id', $type->id)
            ->where('channel', 'mail')
            ->update([
                'is_active' => false,
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
