<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Group\Support\InviteType;
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
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();

                DbTableHelper::privacyColumn($table);
                DbTableHelper::privacyTypeColumn($table);

                $table->unsignedInteger('category_id')
                    ->default(0);

                DbTableHelper::morphUserColumn($table);

                $table->string('name');
                $table->string('profile_name')->nullable();
                $table->string('phone', 15)->nullable();
                $table->string('external_link')->nullable();
                $table->string('landing_page')->default('home');
                $table->unsignedTinyInteger('pending_mode')->default(0);

                DbTableHelper::approvedColumn($table);
                DbTableHelper::featuredColumn($table);
                DbTableHelper::sponsorColumn($table);

                DbTableHelper::totalColumns($table, ['member', 'pending_post']);

                DbTableHelper::morphImage($table, 'avatar');
                DbTableHelper::morphImage($table, 'cover');
                $table->string('cover_photo_position', 10)->nullable();

                DbTableHelper::locationColumn($table);

                $table->boolean('is_rule_confirmation')->default(true);

                $table->boolean('is_answer_membership_question')->default(false);

                $table->timestamps();
            });
        }

        DbTableHelper::categoryTable('group_categories', true);

        if (!Schema::hasTable('group_invites')) {
            Schema::create('group_invites', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedTinyInteger('status_id')->default(0);
                $table->unsignedBigInteger('group_id');
                $table->string('invite_type')->default(InviteType::INVITED_MEMBER);
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                $table->timestamp('expired_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('group_requests')) {
            Schema::create('group_requests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedTinyInteger('status_id')->default(0);
                $table->unsignedBigInteger('group_id');
                DbTableHelper::morphUserColumn($table);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('group_members')) {
            Schema::create('group_members', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('group_id');
                $table->unsignedInteger('member_type')->default(0);
                $table->unsignedTinyInteger('is_muted')->default(0);
                $table->timestamp('mute_expired_at')->nullable();
                DbTableHelper::morphUserColumn($table);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('group_questions')) {
            Schema::create('group_questions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('question')->nullable();
                $table->unsignedBigInteger('group_id');
                $table->unsignedTinyInteger('type_id')->default(0);
            });
        }

        if (!Schema::hasTable('group_question_fields')) {
            Schema::create('group_question_fields', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('question_id');
                $table->string('title')->nullable();
            });
        }

        if (!Schema::hasTable('group_answers')) {
            Schema::create('group_answers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('question_id')->index();
                DbTableHelper::morphUserColumn($table);
                $table->mediumText('value');
            });
        }

        if (!Schema::hasTable('group_rules')) {
            Schema::create('group_rules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('group_id');
                $table->string('title');
                $table->mediumText('description')->nullable();
                $table->unsignedSmallInteger('ordering')->default(0);
            });
        }

        if (!Schema::hasTable('group_rule_examples')) {
            Schema::create('group_rule_examples', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title');
                $table->mediumText('description');
                $table->unsignedTinyInteger('is_active')->default(1);
                $table->unsignedSmallInteger('ordering')->default(0);
            });
        }

        if (!Schema::hasTable('group_blocks')) {
            Schema::create('group_blocks', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('group_id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                $table->timestamps();
            });
        }
        DbTableHelper::streamTables('group');
        DbTableHelper::textTable('group_text');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('group_follows');
        Schema::dropIfExists('group_claim');
        Schema::dropIfExists('group_claims');
        Schema::dropIfExists('group_invites');
        Schema::dropIfExists('group_types');
        Schema::dropIfExists('group_categories');
        Schema::dropIfExists('group_requests');
        Schema::dropIfExists('group_text');
        Schema::dropIfExists('group_questions');
        Schema::dropIfExists('group_question_fields');
        Schema::dropIfExists('group_answers');
        Schema::dropIfExists('group_rules');
        Schema::dropIfExists('group_rule_examples');
        DbTableHelper::dropStreamTables('group');
    }
};
