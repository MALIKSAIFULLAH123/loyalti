<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Announcement\Models\AnnouncementContent;
use MetaFox\Announcement\Models\AnnouncementText;
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
        if (!Schema::hasTable('announcement_contents')) {
            return;
        }

        $upsertData = [];
        $locales    = Language::query()->get()->pluck('language_code')->toArray();

        foreach (AnnouncementText::query()->cursor() as $text) {
            if (!$text instanceof AnnouncementText) {
                continue;
            }

            foreach ($locales as $locale) {
                $upsertData[] = [
                    'announcement_id' => $text->entityId(),
                    'text'            => $text->text,
                    'text_parsed'     => $text->text_parsed,
                    'locale'          => $locale,
                ];
            }
        }

        AnnouncementContent::query()->upsert($upsertData, ['announcement_id', 'locale'], ['text', 'text_parsed']);
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
