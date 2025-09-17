<?php

namespace MetaFox\Word\Support;

use Illuminate\Support\Arr;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\PackageManager;
use MetaFox\Word\Repositories\BlockRepositoryInterface;

class WordSupport
{
    private BlockRepositoryInterface $blockRepository;

    /**
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(BlockRepositoryInterface $blockRepository)
    {
        $this->blockRepository = $blockRepository;
    }

    public function buildBlockWords(): void
    {
        $config = app('files')
            ->getRequire(base_path('packages/framework/word/config/config.php'));

        $this->addBlockWords($config['bad_words'], true);
        $this->blockFromPackageAlias();
        $this->blockFromEntity();
    }

    private function blockFromPackageAlias(): void
    {
        $blockWords = array_map(function (string $name) {
            return PackageManager::getAlias($name);
        }, PackageManager::getPackageNames());

        $this->addBlockWords($blockWords, true);
    }

    private function blockFromEntity(): void
    {
        $words = resolve(DriverRepositoryInterface::class)->getModel()->newQuery()
            ->where('type', 'like', '%entity%')
            ->get('name')
            ->map(function ($item) {
                return [
                    $item->name,
                    preg_split('/(_-)/', $item->name),
                    str_replace('_', '-', $item->name),
                ];
            })
            ->toArray();

        $this->addBlockWords($words, true);
    }

    public function addBlockWords(array $words, bool $system = false): void
    {
        $words = array_unique(Arr::flatten($words));

        foreach ($words as $word) {
            $this->blockRepository->updateOrCreate([
                'word' => $word,
            ], [
                'is_system' => $system,
            ]);
        }
    }

    public function isBlocked(string $word): bool
    {
        return $this->blockRepository
            ->getModel()
            ->newModelQuery()
            ->where('word', $word)
            ->exists();
    }
}
