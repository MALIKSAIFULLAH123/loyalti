<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;
use MetaFox\User\Support\User as UserSupport;

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
        if (Schema::hasTable('user_export_processes')) {
            return;
        }

        Schema::create('user_export_processes', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphUserColumn($table);
            $table->string('filename')->nullable();
            $table->string('path');
            $table->enum('status', UserSupport::allowedStatusExport())->default(UserSupport::EXPORT_STATUS_PENDING);
            $table->json('filters')->nullable();
            $table->json('properties')->nullable();
            DbTableHelper::totalColumns($table, ['user']);
            $table->timestamps();
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
        Schema::dropIfExists('user_export_process');
    }
};
