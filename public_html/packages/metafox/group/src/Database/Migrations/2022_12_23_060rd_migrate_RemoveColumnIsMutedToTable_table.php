<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Group\Repositories\Eloquent\MuteRepository;
use MetaFox\Platform\Support\DbTableHelper;

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
        $this->handleCreateTableMuted();
        $mutedMemberRepository = resolve(MuteRepository::class);
        $mutedMemberRepository->syncUserMuted();
        $this->handleDropColumn();
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('group_muted');
    }

    protected function handleCreateTableMuted(): void
    {
        if (!Schema::hasTable('group_muted')) {
            Schema::create('group_muted', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('group_id');
                $table->unsignedTinyInteger('status')->default(1);
                $table->timestamp('expired_at')->nullable();
                DbTableHelper::morphUserColumn($table);
                $table->timestamps();
            });
        }
    }

    protected function handleDropColumn(): void
    {
        if (Schema::hasTable('group_members')) {
            Schema::table('group_members', function (Blueprint $table) {
                $table->dropColumn(['is_muted', 'mute_expired_at']);
            });
        }
    }
};
