<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Core\Support\Facades\Language;
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

        $locales = Language::getAllLocales();
        Phrase::withoutEvents(function () use ($locales) {
            $textUpdate = '%s <a href="/policy">%s</a>';
            $keyUpdate  = 'core::web.cookie_gdpr_message';
            $keyValue   = 'core::web.cookie_policy';

            foreach ($locales as $locale) {
                $phraseValue = Phrase::query()
                    ->where('key', $keyValue)
                    ->where('locale', '=', $locale)
                    ->first();

                //If this phrase is deleted or not translated to the locale, then return.
                if (!$phraseValue instanceof Phrase) {
                    continue;
                }

                $value     = $phraseValue->text ?? $phraseValue->default_text;
                $phraseKey = Phrase::query()
                    ->where('key', $keyUpdate)
                    ->where('locale', '=', $locale)
                    ->first();

                if (!$phraseKey instanceof Phrase) {
                    continue;
                }

                $phraseKey->update(['text' => sprintf($textUpdate, $phraseKey->text, $value)]);
            }
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {}
};
