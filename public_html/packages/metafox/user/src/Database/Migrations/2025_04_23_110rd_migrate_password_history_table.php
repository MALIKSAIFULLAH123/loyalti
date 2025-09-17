<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\User\Models\UserPasswordHistory;
use MetaFox\User\Models\User;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('user_password_histories')) {
            Schema::create('user_password_histories', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table, true);
                $table->string('password', 255);
                $table->timestamps();
            });
        }

        $userPasswordHistory = UserPasswordHistory::query()->exists();
        if (empty($userPasswordHistory)) {
            $userPasswordQuery =  User::query()
                ->select(
                    'id AS user_id',
                    DB::raw("'user' AS user_type"),
                    'password',
                    DB::raw('now() as created_at'),
                    DB::raw('now() as updated_at')
                )
                ->whereNotNull('password');

            UserPasswordHistory::query()->insertUsing(
                ['user_id', 'user_type', 'password', 'created_at', 'updated_at'],
                $userPasswordQuery
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('user_password_histories');
    }
};
