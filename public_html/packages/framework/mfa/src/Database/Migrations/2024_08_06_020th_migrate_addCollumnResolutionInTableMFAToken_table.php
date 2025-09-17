<?php

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
        if (Schema::hasColumn('mfa_user_auth_tokens', 'resolution')) {
            return;
        }

        Schema::table('mfa_user_auth_tokens', function (Blueprint $table) {
            $table->string('resolution')->default(\MetaFox\Platform\MetaFoxConstant::RESOLUTION_WEB);
        });

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropColumns('mfa_user_auth_tokens', 'resolution');
    }
};
