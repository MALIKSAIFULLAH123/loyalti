<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\StaticPage\Models\StaticPage;
use MetaFox\StaticPage\Models\StaticPageContent;
use MetaFox\Localize\Models\Language;

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
        if (!Schema::hasTable('static_pages')) {
            return;
        }

        if (!Schema::hasTable('static_page_contents')) {
            return;
        }

        $upsertData = [];
        $locales    = Language::query()->get()->pluck('language_code')->toArray();

        foreach (StaticPage::query()->cursor() as $staticPage) {
            if (!$staticPage instanceof StaticPage) {
                continue;
            }

            foreach ($locales as $locale) {
                $upsertData[] = [
                    'static_page_id' => $staticPage->entityId(),
                    'text'           => $staticPage->text ?? '',
                    'locale'         => $locale,
                ];
            }
        }

        StaticPageContent::query()->upsert($upsertData, ['static_page_id', 'locale'], ['text']);

        Schema::table('static_pages', function (Blueprint $table) {
            $table->dropColumn('text');
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
