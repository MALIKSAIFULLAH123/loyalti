<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;

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
        $settingNames = [
            'core.general.keywords',
            'core.general.description',
        ];

        $settings = SiteSetting::query()
            ->whereIn('name', $settingNames)
            ->get();

        $repository = resolve(PhraseRepositoryInterface::class);

        foreach ($settings as $setting) {
            if (__is_phrase($setting->getValue())) {
                continue;
            }

            $phraseKey  = toTranslationKey('core', 'translatable',  $this->generatePhraseName($setting));

            $repository->deletePhraseByKey($phraseKey);
        }
    }

    protected function generatePhraseName(SiteSetting $setting): string
    {
        return sprintf('%s_%s_%s', $setting->entityType(), $setting->entityId(), $setting->name);
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
