<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Support\CustomField;

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
        Field::query()
            ->whereIn('field_name', ['about_me', 'bio', 'interest', 'hobbies'])
            ->update([
                'edit_type' => CustomField::RICH_TEXT_EDITOR,
            ]);

        // to do here
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
