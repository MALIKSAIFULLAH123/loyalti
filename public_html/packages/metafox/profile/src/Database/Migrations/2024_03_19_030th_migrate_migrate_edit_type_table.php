<?php

use Illuminate\Database\Migrations\Migration;
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
        if (!Schema::hasTable('migrate_edit_type')) {
            return;
        }

        \MetaFox\Profile\Models\Field::query()
            ->where(['edit_type' => \MetaFox\Profile\Support\CustomField::DATE])
            ->update(['edit_type' => \MetaFox\Profile\Support\CustomField::BASIC_DATE]);
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
