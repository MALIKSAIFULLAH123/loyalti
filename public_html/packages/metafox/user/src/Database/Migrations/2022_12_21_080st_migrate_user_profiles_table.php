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
        if (Schema::hasTable('user_profiles')) {
            $columns =  array_filter(['about_me', 'hobbies', 'interest', 'bio', 'address'], function ($column) {
                return Schema::hasColumn('user_profiles', $column);
            });
            if (count($columns)) {
                Schema::dropColumns('user_profiles', $columns);
            }
        }
    }
};
