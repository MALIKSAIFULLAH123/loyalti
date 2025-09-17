<?php

namespace MetaFox\Localize\Support;

use DirectoryIterator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use MetaFox\Core\Constants;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\PackageManager;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/**
 * Class TranslationHelper.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.EmptyCatchBlock)
 */
class TranslationHelper
{
    /**
     * @var PhraseRepositoryInterface
     */
    private PhraseRepositoryInterface $repository;

    /**
     * `     * @param PhraseRepositoryInterface $repository
     */
    public function __construct(PhraseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $path
     *
     * @return string[]
     */
    private function getFolders(string $path): array
    {
        $folders = [];

        if (!File::isDirectory($path)) {
            return $folders;
        }

        $iterator = new DirectoryIterator($path);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                $folders[] = $fileInfo->getBasename();
            }
        }

        return $folders;
    }

    /**
     * @param  string   $path
     * @return string[]
     */
    private function getFiles(string $path): array
    {
        $files = [];
        foreach (File::files($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $files[] = $file->getFilenameWithoutExtension();
        }

        return $files;
    }

    /**
     * @param  string          $namespace
     * @param  string          $path
     * @return array<string[]>
     */
    private function inspectLangStructure(string $namespace, string $path): array
    {
        $result  = [];
        $locales = $this->getFolders($path);
        foreach ($locales as $locale) {
            $localeDir = $path . DIRECTORY_SEPARATOR . $locale;
            foreach ($this->getFiles($localeDir) as $group) {
                $result[] = [$locale, $namespace, $group, $localeDir . DIRECTORY_SEPARATOR . $group . '.php'];
            }
            foreach ($this->getFolders($localeDir) as $domain) {
                $domainDir = $localeDir . DIRECTORY_SEPARATOR . $domain;
                foreach ($this->getFiles($domainDir) as $group) {
                    $result[] = [$locale, $domain, $group, $domainDir . DIRECTORY_SEPARATOR . $group . '.php'];
                }
            }
        }

        return $result;
    }

    public function importTranslations(string $package, string $namespace, string $path): void
    {
        $dataInsert = [];
        Log::channel('installation')->debug('migrate languages ', [$namespace, $path]);

        $results = $this->inspectLangStructure($namespace, $path);

        foreach ($results as $result) {
            [$locale, $namespace, $group, $file] = $result;
            $lines                               = $this->getLanguagePhpFile($file);

            if (!is_array($lines) || empty($lines)) {
                continue;
            }
            $namespaceId = $namespace === '_root' ? '*' : $namespace;
            $groupId     = $group === '_root' ? '*' : $group;
            $data        = $this->convertToSimpleArray([$group => $lines]);

            foreach ($data as $name => $text) {
                $dataInsert[] = [
                    'key'          => toTranslationKey($namespaceId, $groupId, $name),
                    'name'         => $name,
                    'package_id'   => $package,
                    'group'        => $groupId,
                    'namespace'    => $namespaceId,
                    'locale'       => $locale,
                    'text'         => $text,
                    'default_text' => $text,
                ];
            }
        }

        if (!empty($dataInsert)) {
            $this->repository->getModel()->newQuery()
                ->upsert($this->excludeModified($dataInsert), ['key', 'locale'], ['package_id', 'text', 'default_text']);

            $this->repository->getModel()->newQuery()
                ->upsert($this->onlyModified($dataInsert), ['key', 'locale'], ['default_text']);
        }
    }

    /**
     * @param  array<string,string> $arr
     * @return array<string,string>
     */
    public function convertToSimpleArray(array $arr): array
    {
        $iterator    = new RecursiveIteratorIterator(new RecursiveArrayIterator($arr));
        $simpleArray = [];
        foreach ($iterator as $leafValue) {
            $keys = [];
            foreach (range(1, $iterator->getDepth()) as $depth) {
                $keys[] = $iterator->getSubIterator($depth)->key();
            }
            $simpleArray[implode('.', $keys)] = $leafValue;
        }

        return $simpleArray;
    }

    /**
     * @param  string                     $file
     * @return array<string,string>|mixed
     */
    private function getLanguagePhpFile(string $file)
    {
        try {
            if (app('files')->exists($file)) {
                return app('files')->getRequire($file);
            }
        } catch (FileNotFoundException $e) {
        }

        return [];
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
            ])->distinct()->get();

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

    /**
     * Export translation to csv file.
     *
     * @param  string $filename
     * @param  string $locale
     * @param  string $base
     * @return void
     */
    public function exportTranslationsCSV(string $filename, string $locale, string $base = 'en'): void
    {
        $stream = fopen($filename, 'w');

        if (!$stream) {
            throw new RuntimeException('Could not open ' . $filename);
        }

        fputcsv($stream, ['key', 'locale', 'package', 'origin_text', 'text']);

        $limit  = 500;
        $offset = 0;
        do {
            $rows = Phrase::query()
                ->from('phrases', 'base')
                ->select(['base.key', 'base.locale', 'base.package_id', 'base.default_text as origin_text', 'derived.text'])
                ->leftJoin('phrases as derived', function (JoinClause $join) use ($locale) {
                    $join->on('base.key', '=', 'derived.key');
                    $join->where('derived.locale', $locale);

                    return $join;
                })
                ->whereNotNull('base.text')
                ->where('base.locale', '=', $base)
                ->orderBy('base.key');

            $rows = $rows->limit($limit)->offset($offset)->cursor();

            foreach ($rows as $row) {
                fputcsv($stream, [$row->key, $locale, $row->package_id, $row->origin_text, $row->text ?: $row->origin_text]);
            }
            $offset += $limit;
        } while ($rows->count() > 0);
    }

    /**
     * Import translation from csv file.
     *
     * @param  string $path
     * @return void
     */
    public function importTranslationsFromCSV(string $path)
    {
        if (!app('files')->exists($path)) {
            return;
        }

        $repository = resolve(PhraseRepositoryInterface::class);
        $data       = csv_to_multi_array($path);

        if (empty($data)) {
            $this->throwErrorFileValidation();
        }

        $translator = app('translator');
        $collects   = [];

        foreach ($data as $row) {
            $key         = $row['key'] ?? null;
            $text        = $row['text'] ?? '';
            $originText  = $row['origin_text'] ?? '';
            $defaultText = $translator->get($key, [], 'en');
            $locale      = $row['locale'] ?? null;
            $packageId   = $row['package'] ?? 'metafox/core';

            if (!$key || !$locale) {
                continue;
            }

            if (!$text) {
                // the original text should be set as default if the text is falsy
                $text = $originText;
            }

            if ($defaultText === $key) {
                // in case the platform installation process hasn't been completed yet
                // the system won't be able to translate the default text
                // it should be set back to the original text as the default option.
                $defaultText = $originText;
            }

            // batch insert or updates
            [$namespace, $group, $name] = $translator->parseKey($key);

            if (empty($namespace) || empty($group) || empty($name)) {
                Log::error([__p('localize::phrase.import_key_invalid', ['phraseKey' => $key])]);
                continue;
            }

            $collects[] = [
                'key'          => $key,
                'locale'       => $locale,
                'text'         => $text,
                'default_text' => $defaultText ?: $originText,
                'namespace'    => $namespace,
                'group'        => $group,
                'name'         => $name,
                'package_id'   => $packageId,
            ];
        }

        if (empty($collects)) {
            $this->throwErrorFileValidation();
        }

        // Reduce sql packet size
        $chunks = array_chunk($collects, 20);

        // save memory
        unset($data);
        unset($collects);

        foreach ($chunks as $chunk) {
            try {
                $repository->getModel()->newQuery()
                    ->upsert($this->excludeModified($chunk), ['key', 'locale'], ['package_id', 'text', 'default_text']);

                $repository->getModel()->newQuery()
                    ->upsert($this->onlyModified($chunk), ['key', 'locale'], ['default_text']);
            } catch (\Throwable $th) {
                Log::error('Cannot import this chunk', $chunk);
                Log::error($th->getMessage());
            }
        }
    }

    private function throwErrorFileValidation(): void
    {
        throw ValidationException::withMessages([
            'file' => __p('validation.file_contains_invalid_content'),
        ]);
    }

    public function excludeModified($data)
    {
        if (!count($data)) {
            return [];
        }

        $keys = array_map(function ($data) {
            return $data['key'];
        }, $data);

        $locale = $data[0]['locale'];
        $keys   = Phrase::query()
            ->whereIn('key', $keys)
            ->where('locale', '=', $locale)
            ->where('is_modified', '=', 1)
            ->pluck('key')
            ->toArray();

        if (!count($keys)) {
            return $data;
        }

        return array_filter($data, function ($item) use ($keys) {
            return !in_array($item['key'], $keys);
        });
    }

    public function onlyModified($data)
    {
        if (!count($data)) {
            return [];
        }

        $keys = array_map(function ($data) {
            return $data['key'];
        }, $data);

        $locale = $data[0]['locale'];
        $keys   = Phrase::query()
            ->whereIn('key', $keys)
            ->where('locale', '=', $locale)
            ->where('is_modified', '=', 1)
            ->pluck('key')
            ->toArray();

        if (!count($keys)) {
            return $data;
        }

        return array_filter($data, function ($item) use ($keys) {
            return in_array($item['key'], $keys);
        });
    }
}
