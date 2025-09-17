<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Models\User;

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
        if (Schema::hasColumn('users', 'approve_status')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('approve_status', [
                MetaFoxConstant::STATUS_APPROVED,
                MetaFoxConstant::STATUS_NOT_APPROVED,
                MetaFoxConstant::STATUS_PENDING_APPROVAL,
            ])->default(MetaFoxConstant::STATUS_APPROVED);
        });

        $this->handleSyncData();
        $this->handleDeleteColumnIsApprove();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    protected function handleSyncData(): void
    {
        User::query()->where('is_approved', 1)
            ->update(['approve_status' => MetaFoxConstant::STATUS_APPROVED]);

        User::query()->where('is_approved', 0)
            ->update(['approve_status' => MetaFoxConstant::STATUS_PENDING_APPROVAL]);
    }

    protected function handleDeleteColumnIsApprove(): void
    {
        Schema::dropColumns('users', 'is_approved');
    }
};
