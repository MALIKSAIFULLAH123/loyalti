<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * @ignore
 * @codeCoverageIgnore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //phpfox_pages

        $this->createPageTable();

        $this->createPageCategoryTable();

        $this->createPageClaimTable();

        $this->createPageInviteTable();

        $this->createPageMemberTable();

        $this->createPageBlockTable();

        DbTableHelper::streamTables('page');
        DbTableHelper::textTable('page_text');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_types');
        Schema::dropIfExists('page_invites');
        Schema::dropIfExists('page_likes');
        Schema::dropIfExists('page_members');
        Schema::dropIfExists('page_follows');
        Schema::dropIfExists('page_claim');
        Schema::dropIfExists('page_claims');
        Schema::dropIfExists('page_members');
        Schema::dropIfExists('page_categories');
        Schema::dropIfExists('page_text');
        Schema::dropIfExists('page_blocks');
        DbTableHelper::dropStreamTables('page');
    }

    private function createPageTable(): void
    {
        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->unsignedInteger('category_id')->default(0);

                DbTableHelper::privacyColumn($table);
                DbTableHelper::morphUserColumn($table);
                $table->string('name');
                $table->string('profile_name')
                    ->nullable()->index();
                $table->string('phone', 15)->nullable();
                $table->string('external_link')->nullable();
                $table->string('landing_page')->nullable();

                DbTableHelper::approvedColumn($table);
                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);

                DbTableHelper::morphImage($table, 'avatar');
                DbTableHelper::morphImage($table, 'cover');
                $table->string('cover_photo_position', 10)->nullable();

                DbTableHelper::totalColumns($table, ['member', 'share']);
                DbTableHelper::locationColumn($table);
                $table->timestamps();
            });
        }
    }

    private function createPageCategoryTable(): void
    {
        DbTableHelper::categoryTable('page_categories', true);
    }

    private function createPageClaimTable(): void
    {
        if (!Schema::hasTable('page_claims')) {
            Schema::create('page_claims', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedTinyInteger('status_id')
                    ->default(0);

                $table->unsignedBigInteger('page_id')
                    ->default(0)->index();

                DbTableHelper::morphUserColumn($table);

                $table->text('message')->nullable();

                $table->timestamps();
            });
        }
    }

    private function createPageInviteTable(): void
    {
        if (!Schema::hasTable('page_invites')) {
            Schema::create('page_invites', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedTinyInteger('status_id')
                    ->default(0);

                $table->unsignedBigInteger('page_id')
                    ->index();

                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);

                $table->timestamp('expired_at');
                $table->timestamps();
            });
        }
    }

    private function createPageMemberTable(): void
    {
        if (!Schema::hasTable('page_members')) {
            Schema::create('page_members', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('page_id')
                    ->index();
                $table->unsignedInteger('member_type')->default(0);
                DbTableHelper::morphUserColumn($table);
                $table->timestamps();
            });
        }
    }

    private function createPageBlockTable(): void
    {
        if (!Schema::hasTable('page_blocks')) {
            Schema::create('page_blocks', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('page_id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                $table->timestamps();
            });
        }
    }
};
