<?php

namespace MetaFox\Localize\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Constants;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\PackageManager;

class PackageTranslationExporter
{
    /**
     * @var PhraseRepositoryInterface
     */
    private PhraseRepositoryInterface $repository;

    /**
     * @param PhraseRepositoryInterface $repository
     */
    public function __construct(PhraseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $package
     * @param string $locale
     *
     * @return string[]
     */
    private function getNamespaces(string $package, string $locale): array
    {
        /** @var Collection<Phrase> $namespaceArr */
        $namespaceArr = $this->repository->getModel()->newQuery()
            ->select('namespace')
            ->where(['locale' => $locale, 'package_id' => $package])
            ->distinct()
            ->get();

        return $namespaceArr->map(function (Phrase $item) {
            return $item->namespace;
        })->toArray();
    }

    /**
     * @param string $package
     * @param string $locale
     * @param string $namespace
     *
     * @return string[]
     */
    private function getGroups(string $package, string $locale, string $namespace): array
    {
        /** @var Collection<Phrase> $groupArr */
        $groupArr = $this->repository->getModel()->newQuery()
            ->select('group')
            ->where([
                'locale'     => $locale,
                'package_id' => $package,
                'namespace'  => $namespace,
            ])
            ->where('group', '<>', 'translatable')
            ->distinct()
            ->get();

        return $groupArr->map(function (Phrase $item) {
            return $item->group;
        })->toArray();
    }

    /**
     * @param string $package
     * @param string $locale
     * @param string $namespace
     * @param string $group
     *
     * @return array<string,string>`
     */
    private function getPhrases(string $package, string $locale, string $namespace, string $group): array
    {
        $phrases = [];
        $query   = $this->repository->getModel()->newModelQuery()
            ->where([
                'namespace'  => $namespace,
                'package_id' => $package,
                'group'      => $group,
                'locale'     => $locale,
            ])->orderBy('name');

        foreach ($query->cursor() as $item) {
            Arr::set($phrases, $item->name, $item->text);
        }

        return $phrases;
    }

    /**
     * @param string               $filename
     * @param array<string,string> $phrases
     *
     * @return void
     */
    private function dumpPhrases(string $filename, array $phrases): void
    {
        $dir = dirname($filename);

        if (!app('files')->exists($dir)) {
            app('files')->makeDirectory($dir, 0755, true, false);
        }

        export_to_file($filename, $phrases);

        Log::channel('installation')->info('export translation to ' . $filename);
    }

    /**
     * @param string $package
     * @param string $locale
     * @param string $baseLocale
     *
     * @return array<string>
     */
    public function exportTranslations(string $package, string $locale = 'en', string $baseLocale = 'en'): array
    {
        $response   = [];
        $namespaces = $this->getNamespaces($package, $baseLocale);

        $path = base_path(
            implode(DIRECTORY_SEPARATOR, [PackageManager::getPath($package), 'resources', 'lang'])
        );
        $alias = PackageManager::getAlias($package);

        // cleanup
        if (is_dir($path)) {
            app('files')->deleteDirectories($path);
        }

        foreach ($namespaces as $namespace) {
            $groups = $this->getGroups($package, $baseLocale, $namespace);

            foreach ($groups as $group) {
                $domainFolder = $namespace === '*' ? Constants::LOCALE_NAMESPACE_ROOT : $namespace;
                $groupFile    = $group === '*' ? Constants::LOCALE_GROUP_ROOT : $group;

                $filename = implode(DIRECTORY_SEPARATOR, [
                    $path,
                    $locale,
                    $domainFolder,
                    "$groupFile.php",
                ]);

                if ($domainFolder === $alias) {
                    $filename = implode(DIRECTORY_SEPARATOR, [
                        $path,
                        $locale,
                        "$groupFile.php",
                    ]);
                }

                $response[] = $filename;
                $this->dumpPhrases($filename, $this->getPhrases($package, $baseLocale, $namespace, $group));
            }
        }

        return $response;
    }
}
