<?php

namespace MetaFox\Localize\Support;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Translation\FileLoader;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Platform\PackageManager;

/**
 * Class TranslationLoader.
 */
class TranslationLoader extends FileLoader
{
    /** @var string */
    private string $fallbackLocale = 'en';

    public function load($locale, $group, $namespace = null)
    {
        if (!config('app.mfox_installed')) {
            return [];
        }

        $fallbackLocale = $this->fallbackLocale;
        $mode           = $this->getTranslationMode();

        return localCacheStore()->rememberForever(
            "locale.phrases.{$namespace}.{$locale}.{$group}.{$mode}",
            function () use ($fallbackLocale, $locale, $group, $namespace) {
                return $this->loadPhrases($fallbackLocale, $locale, $group, $namespace);
            }
        );
    }

    private function loadPhrases($fallbackLocale, $locale, $group, $namespace): array
    {
        return $locale === $fallbackLocale ? $this->getGroupOfBaseLocale($locale, $group, $namespace) :
            $this->getGroupOfDerivedLocale($locale ?? config('app.locale'), $fallbackLocale, $group, $namespace);
    }

    private function getGroupOfBaseLocale(string $locale, string $group, ?string $namespace): array
    {
        $result = [];
        $wheres = [
            ['phrases.locale', '=', $locale],
        ];

        if ($group) {
            $wheres[] = ['phrases.group', '=', $group];
        }
        if ($namespace) {
            $wheres[] = ['phrases.namespace', '=', $namespace];
        }

        $query = Phrase::query()
            ->select(['phrases.text', 'phrases.name', 'phrases.package_id', 'phrases.namespace', 'phrases.group', 'phrases.key'])
            ->join('packages', function (JoinClause $join) {
                $join->on('phrases.package_id', '=', 'packages.name');
                $join->where('packages.is_active', 1);
            })
            ->where($wheres)
            ->orderBy('phrases.id');

        foreach ($query->cursor() as $item) {
            if (!$item instanceof Phrase) {
                continue;
            }

            $key = Arr::has($result, $item->name) ? $this->transformKey($item) : $item->name;

            Arr::set($result, $key, __translation_wrapper(__translation_prefix($item->key, $item->text)));
        }

        return $result;
    }

    private function getGroupOfDerivedLocale(
        string $locale,
        string $fallbackLocale,
        string $group,
        ?string $namespace
    ): array {
        $result = [];
        $wheres = [
            ['base.locale', '=', $fallbackLocale],
        ];

        if ($group) {
            $wheres[] = ['base.group', '=', $group];
        }
        if ($namespace) {
            $wheres[] = ['base.namespace', '=', $namespace];
        }

        $query = Phrase::query()
            ->from('phrases', 'base')
            ->select([
                'base.key', 'base.locale', 'base.name', 'base.group', 'base.namespace', 'base.package_id', 'base.text as base_text',
                'derived.text',
            ])
            ->leftJoin('phrases as derived', function (JoinClause $join) use ($locale) {
                $join->on('base.key', '=', 'derived.key');
                $join->where('derived.locale', $locale);

                return $join;
            })
            ->join('packages', function (JoinClause $join) {
                $join->on('base.package_id', '=', 'packages.name');
                $join->where('packages.is_active', 1);
            })
            ->where($wheres);

        foreach ($query->cursor() as $item) {
            if (!$item instanceof Phrase) {
                continue;
            }

            $key = Arr::has($result, $item->name) ? $this->transformKey($item) : $item->name;

            Arr::set($result, $key, __translation_wrapper(__translation_prefix($item->key, $item->text ?? $item->base_text)));
        }

        return $result;
    }

    protected function transformKey(Phrase $phrase): ?string
    {
        $isCorePhrase = PackageManager::isCore($phrase->package_id) ?: false;

        $keepAsItIs = $isCorePhrase || $phrase->group !== 'web' || mb_strpos($phrase->name, $phrase->namespace . '_') === 0;

        return $keepAsItIs ? $phrase->name : sprintf('%s_%s', $phrase->namespace, $phrase->name);
    }

    public function getTranslationMode(): string
    {
        $default = 'view';
        $mode    = config('localize.view_mode', $default);

        return is_string($mode) ? $mode : $default;
    }
}
