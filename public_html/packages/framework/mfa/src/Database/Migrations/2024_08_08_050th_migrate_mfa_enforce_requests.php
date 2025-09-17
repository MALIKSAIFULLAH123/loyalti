<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Mfa\Models\EnforceRequest;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('mfa_enforce_requests')) {
            return;
        }

        Schema::create('mfa_enforce_requests', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphUserColumn($table);
            $table->unsignedTinyInteger('is_active')->default(0);
            $table->enum('enforce_status', [
                    EnforceRequest::STATUS_SUCCESS,
                    EnforceRequest::STATUS_BLOCKED,
                    EnforceRequest::STATUS_CANCELLED,
                    EnforceRequest::STATUS_FORCED,
            ])->nullable()->default(null);
            $table->timestamps();
            $table->timestamp('due_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('mfa_user_requests');
    }
};
