<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Localize\Models\Phrase;

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
        if (!Schema::hasTable('phrases')) {
            return;
        }

        Phrase::withoutEvents(function () {
            Phrase::query()
                ->where('locale', 'en')
                ->where('group', 'translatable')
                ->whereNot('text', '')
                ->where('default_text', '')->lazyById()->each(function ($phrase) {
                    if (!$phrase instanceof Phrase) {
                        return true;
                    }

                    $phrase->update(['default_text' => $phrase->text]);
                });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Nothing to do
    }
};
