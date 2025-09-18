<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Authorization\Models\Role;
use MetaFox\Localize\Models\Country;
use MetaFox\Newsletter\Models\Newsletter;
use MetaFox\User\Models\UserGender;

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
        if (!Schema::hasTable('newsletters')) {
            return;
        }

        if (!Schema::hasColumns('newsletters', ['user_roles', 'gender_id', 'country_iso'])) {
            return;
        }

        $this->addDataTable();

        $this->migrateData();

        $this->removeOldColumn();
    }

    private function addDataTable(): void
    {
        if (!Schema::hasTable('newsletter_role_data')) {
            Schema::create('newsletter_role_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('newsletter_id')->index();
                $table->unsignedInteger('role_id')->index();
            });
        }

        if (!Schema::hasTable('newsletter_gender_data')) {
            Schema::create('newsletter_gender_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('newsletter_id')->index();
                $table->unsignedInteger('gender_id')->index();
            });
        }

        if (!Schema::hasTable('newsletter_country_data')) {
            Schema::create('newsletter_country_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('newsletter_id')->index();
                $table->char('country_iso', 2)->index();
            });
        }
    }

    private function migrateData(): void
    {
        Newsletter::query()->chunkById(100, function ($records) {
            foreach ($records as $newsletter) {
                if (!$newsletter instanceof Newsletter) {
                    continue;
                }

                $updateData = [];

                $countryIso = $newsletter->getRawOriginal('country_iso');
                if (!empty($countryIso) && Country::query()->where('country_iso', $countryIso)->exists()) {
                    $updateData['countries'] = [$countryIso];
                }

                $genderId = $newsletter->getRawOriginal('gender_id');
                if (!empty($genderId) && UserGender::query()->find($genderId)) {
                    $updateData['genders'] = [$genderId];
                }

                $userRoles = json_decode($newsletter->getRawOriginal('user_roles'));
                if (!empty($userRoles)) {
                    $validRoles = Role::query()->whereIn('id', $userRoles)->pluck('id')->toArray();

                    if (!empty($validRoles)) {
                        $updateData['roles'] = $validRoles;
                    }
                }

                if (empty($updateData)) {
                    continue;
                }

                $newsletter->fill($updateData)->save();
            }
        });
    }

    protected function removeOldColumn(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropColumn(['user_roles', 'gender_id', 'country_iso']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
