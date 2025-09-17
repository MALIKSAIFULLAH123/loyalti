<?php

namespace MetaFox\StaticPage\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use MetaFox\Platform\PackageManager;
use MetaFox\StaticPage\Models\StaticPage;
use MetaFox\StaticPage\Repositories\StaticPageRepositoryInterface;
use MetaFox\Localize\Models\Language;
use MetaFox\StaticPage\Models\StaticPageContent;

/**
 * Class PackageSeeder.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (StaticPage::query()->exists()) {
            return;
        }

        $pages  = resolve(StaticPageRepositoryInterface::class);
        $config = PackageManager::getConfig('metafox/static-page');

        $termPage = $pages->updateOrCreate([
            'slug' => 'term-of-use',
        ], [
            'slug'            => 'term-of-use',
            'title'           => 'Term of Uses',
            'is_active'       => 1,
            'user_id'         => 1,
            'user_type'       => 'user',
            'owner_id'        => 1,
            'owner_type'      => 'user',
            'module_id'       => 'core',
            'disallow_access' => '',
        ]);

        $policyPage =  $pages->updateOrCreate([
            'slug' => 'policy',
        ], [
            'slug'            => 'policy',
            'title'           => 'Privacy',
            'is_active'       => 1,
            'user_id'         => 1,
            'user_type'       => 'user',
            'owner_id'        => 1,
            'owner_type'      => 'user',
            'module_id'       => 'core',
            'disallow_access' => '',
        ]);

        $upsertData = $this->generateUpsertData($config, $termPage, $policyPage);

        StaticPageContent::query()->upsert($upsertData, ['static_page_id', 'locale'], ['text']);
    }

    private function generateUpsertData(array $config, StaticPage $termPage, StaticPage $policyPage): array
    {
        $upsertData = [];
        $locales    = Language::query()->get()->pluck('language_code')->toArray();

        foreach ($locales as $locale) {
            $upsertData[] = [
                'static_page_id' => $termPage->entityId(),
                'text'           => Arr::get($config, 'pages.term', '[YOUR CONTENT HERE]'),
                'locale'         => $locale,
            ];

            $upsertData[] = [
                'static_page_id' => $policyPage->entityId(),
                'text'           => Arr::get($config, 'pages.policy', '[YOUR CONTENT HERE]'),
                'locale'         => $locale,
            ];
        }

        return $upsertData;
    }
}
