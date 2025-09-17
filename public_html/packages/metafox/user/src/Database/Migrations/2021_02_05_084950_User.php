<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;
use MetaFox\User\Models\UserShortcut;

/*
 * @codeCoverageIgnore
 * @ignore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createUserEntitiesTable();

        $this->createUserActivitiesTable();

        $this->createUserPrivacyTypeTable();

        $this->createUserPrivacyResourceTable();

        $this->createUserPrivacyValueTable();

        $this->createUserDeleteReasonTable();

        $this->createUserDeleteFeedbackTable();

        // this table is combine phpfox_user_activity + phpfox_user_count
        $this->createUserValueTable();

        // this table is combine phpfox_user_activity + phpfox_user_count
        $this->createUserBlockedTable();

        $this->createSocialAccountTable();

        $this->createUserBanTable();

        $this->createUserBanFilterTable();

        $this->createPromotionTable();

        $this->createMultiFactorTokenTable();

        $this->createUserRelationTables();

        $this->createUserVerifyErrorTable();

        $this->createUserShortcutTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_entities');
        Schema::dropIfExists('users');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('user_blocked');
        Schema::dropIfExists('user_blocked');
        Schema::dropIfExists('user_values');
        Schema::dropIfExists('user_delete_feedback');
        Schema::dropIfExists('user_delete_reasons');
        Schema::dropIfExists('user_privacy');
        Schema::dropIfExists('user_privacy_values');
        Schema::dropIfExists('user_privacy_types');
        Schema::dropIfExists('user_ban');
        Schema::dropIfExists('user_ban_data');
        Schema::dropIfExists('user_promotion');
        Schema::dropIfExists('user_verify_error');
        Schema::dropIfExists('user_multi_factor_token');
        Schema::dropIfExists('user_relation');
        Schema::dropIfExists('user_activities');

        if (Schema::hasColumn('users', 'is_featured')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_featured');
            });
        }

        Schema::dropIfExists('user_shortcuts');
    }

    private function createUserEntitiesTable(): void
    {
        if (!Schema::hasTable('user_entities')) {
            Schema::create('user_entities', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('entity_type', 50);

                $table->string('user_name', 100)
                    ->unique('user_entities_user_name')->nullable();

                $table->string('name')->nullable();
                $table->string('short_name')->nullable();

                DbTableHelper::morphImage($table, 'avatar');

                $table->tinyInteger('is_featured')->default(0);

                $table->tinyInteger('is_searchable')->default(1);

                $table->tinyInteger('gender')->default(0);

                $table->index(['id', 'entity_type']);
            });
        }
    }

    private function createUserActivitiesTable(): void
    {
        if (!Schema::hasTable('user_activities')) {
            Schema::create('user_activities', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->timestamp('last_login')->nullable();
                $table->timestamp('last_activity')->nullable();
                $table->string('last_ip_address')->nullable();
            });
        }
    }

    private function createUserPrivacyTypeTable(): void
    {
        if (!Schema::hasTable('user_privacy_types')) {
            Schema::create('user_privacy_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->unsignedTinyInteger('privacy_default')
                    ->default(0);
            });
        }
    }

    private function createUserPrivacyResourceTable(): void
    {
        if (!Schema::hasTable('user_privacy_resources')) {
            Schema::create('user_privacy_resources', function (Blueprint $table) {
                $table->increments('id');
                $table->string('entity_type')->index();
                $table->string('type_id', 50)->index();
                $table->text('phrase')->nullable();
                $table->unsignedTinyInteger('privacy_default')
                    ->default(0);
//                $table->string('option_values')
//                    ->comment('json serialized');
                // can share blogs on profile
                // user.1 => option_values [0=>anyone, 1=>friends,]
                // page.1 => option_values [0=>anyone, 1=> page_member']
                // group.2 => option_values [0=>anyone, 1=> group_member']
            });
        }
    }

    private function createUserPrivacyValueTable(): void
    {
        if (!Schema::hasTable('user_privacy_values')) {
            Schema::create('user_privacy_values', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->string('type_id', 50)->index();
                $table->string('name');
                DbTableHelper::privacyColumn($table);
                DbTableHelper::privacyIdColumn($table, true);
            });
        }
    }

    private function createUserDeleteReasonTable(): void
    {
        if (!Schema::hasTable('user_delete_reasons')) {
            Schema::create('user_delete_reasons', function (Blueprint $table) {
                $table->increments('id');

                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);

                $table->string('phrase_var', 255)->nullable();

                $table->boolean('is_active')->default(1);

                $table->unsignedInteger('ordering')->default(0);

                $table->timestamps();
            });
        }
    }

    private function createUserDeleteFeedbackTable(): void
    {
        if (!Schema::hasTable('user_delete_feedback')) {
            Schema::create('user_delete_feedback', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('email', 255);
                $table->string('name', 255);
                $table->unsignedInteger('user_group_id');
                $table->mediumText('feedback_text');
                $table->unsignedInteger('reason_id')->nullable();
                $table->mediumText('reasons_given')->nullable();

                DbTableHelper::morphUserColumn($table);

                $table->timestamps();
            });
        }
    }

    private function createUserValueTable(): void
    {
        if (!Schema::hasTable('user_values')) {
            Schema::create('user_values', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->string('name');
                $table->unsignedBigInteger('value');
                $table->unsignedBigInteger('default_value');
                $table->unsignedBigInteger('ordering')->default(0);
            });
        }
    }

    private function createUserBlockedTable(): void
    {
        if (!Schema::hasTable('user_blocked')) {
            Schema::create('user_blocked', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                $table->timestamps();
            });
        }
    }

    private function createUserVerifyErrorTable(): void
    {
        if (!Schema::hasTable('user_verify_error')) {
            Schema::create('user_verify_error', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('hash_code', 255);
                $table->string('ip_address', 32);
                $table->string('email', 255);
                $table->timestamps();
            });
        }
    }

    private function createUserVerifyTable(): void
    {
    }

    private function createMultiFactorTokenTable(): void
    {
        if (!Schema::hasTable('user_multi_factor_token')) {
            Schema::create('user_multi_factor_token', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('email', 255);
                $table->string('hash_code', 255);
                $table->timestamps();
            });
        }
    }

    private function createUserRelationTables(): void
    {
        if (!Schema::hasTable('user_relation')) {
            Schema::create('user_relation', function (Blueprint $table) {
                $table->increments('id');
                $table->string('phrase_var', 255);
                $table->unsignedTinyInteger('confirm')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_relation_data')) {
            Schema::create('user_relation_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('relation_id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphColumn($table, 'with_user');
                $table->unsignedTinyInteger('status_id');
                $table->unsignedBigInteger('total_like')->default(0);
                $table->unsignedBigInteger('total_comment')->default(0);
                $table->timestamps();
            });
        }
    }

    private function createPromotionTable(): void
    {
        if (!Schema::hasTable('user_promotion')) {
            Schema::create('user_promotion', function (Blueprint $table) {
                $table->increments('id');
                $table->string('user_group_id');
                $table->string('upgrade_user_group_id');
                $table->unsignedTinyInteger('is_active')->default(0);
                $table->unsignedInteger('total_activity')->default(0);
                $table->unsignedInteger('total_day')->default(0);
                $table->timestamps();
            });
        }
    }

    private function createSocialAccountTable(): void
    {
        if (!Schema::hasTable('social_accounts')) {
            Schema::create('social_accounts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('provider_user_id');
                $table->string('provider');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                // indexing
                $table->index(['provider_user_id']);
                $table->index(['user_id']);
            });
        }
    }

    private function createUserBanTable(): void
    {
        if (!Schema::hasTable('user_ban')) {
            Schema::create('user_ban', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                $table->unsignedInteger('ban_id')->nullable();

                $table->integer('start_time_stamp');
                $table->integer('end_time_stamp');

                $table->bigInteger('return_user_group');
                $table->text('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createUserBanFilterTable(): void
    {
        if (!Schema::hasTable('user_ban_rules')) {
            Schema::create('user_ban_rules', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->string('type_id', 100);
                $table->mediumText('find_value');
                $table->mediumText('replacement')->nullable();
                $table->unsignedTinyInteger('ban_user')->default(0);
                $table->unsignedInteger('day_banned')->default(0);
                $table->unsignedInteger('return_user_group')->nullable();
                $table->text('reason')->nullable();
                $table->mediumText('user_group_effected')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createUserShortcutTable(): void
    {
        if (!Schema::hasTable('user_shortcuts')) {
            Schema::create('user_shortcuts', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphItemColumn($table);
                $table->tinyInteger('sort_type')->default(UserShortcut::SORT_DEFAULT)->index();
                $table->timestamps();
            });
        }
    }
};
