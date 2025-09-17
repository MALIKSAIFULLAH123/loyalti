<?php

use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use Illuminate\Database\Migrations\Migration;
use MetaFox\Platform\Facades\Settings;

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
        $this->removePhrase();
        $this->removeSetting();
    }

    protected function removePhrase(): void
    {
        $phraseKeys = [
            'mobile::phrase.smart_banner_title',
            'mobile::phrase.smart_banner_title_phrase',
            'mobile::phrase.smart_banner_desc',
            'mobile::phrase.smart_banner_desc_phrase',
        ];

        $phraseRepository = resolve(PhraseRepositoryInterface::class);

        foreach ($phraseKeys as $key) {
            $phraseRepository->deletePhraseByKey($key);
        }
    }

    protected function removeSetting(): void
    {
        $settingNames = [
            'mobile.smart_banner_title',
            'mobile.smart_banner_desc',
        ];

        Settings::destroy('mobile', $settingNames);
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
