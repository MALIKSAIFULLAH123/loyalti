<?php

namespace MetaFox\Localize\Repositories\Eloquent;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Localize\Support\Browse\Scopes\Phrase\ViewScope;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * @method Phrase find($id, $columns = ['*'])
 * @method Phrase getModel()
 */
class PhraseRepository extends AbstractRepository implements PhraseRepositoryInterface
{
    public function model(): string
    {
        return Phrase::class;
    }

    public function translationOf(?string $key, string $locale = null): ?string
    {
        if (!$key) {
            return null;
        }

        if (!$locale) {
            $locale = config('app.locale', 'en');
        }

        /** @var ?Phrase $model */
        $model = $this->getModel()->newQuery()
            ->join('packages', function (JoinClause $join) {
                $join->on('phrases.package_id', '=', 'packages.name');
                $join->where('packages.is_active', 1);
            })
            ->where(['phrases.key' => $key, 'phrases.locale' => $locale])->first();
        if (!$model instanceof Phrase) {
            return null;
        }

        return $model->text ? __translation_wrapper(__translation_prefix($model->key, $model->text)) : null;
    }

    public function updatePhrases(array $data, bool $dryRun = false): void
    {
        foreach ($data as $key => $text) {
            $this->addSamplePhrase($key, $text, null, false, true);
        }
    }

    public function addSamplePhrase(
        string  $key,
        ?string $text = null,
        ?string $locale = null,
        bool    $dryRun = false,
        bool    $overwrite = false
    ): bool
    {
        if (!$locale) {
            $locale = config('app.locale', 'en');
        }

        [$namespace, $group, $name] = app('translator')->parseKey($key);

        if ($group === '*') {
            return false;
        }
        if ($namespace === '*') {
            return false;
        }

        if ($dryRun) {
            return true;
        }

        if (!$overwrite && $this->checkExistKey($key, $locale)) {
            return false;
        }

        $this->updateOrCreate([
            'locale' => $locale,
            'key'    => $key,
        ], [
            'namespace'  => $namespace,
            'group'      => $group,
            'key'        => $key,
            'name'       => $name,
            'locale'     => $locale,
            'package_id' => PackageManager::getByAlias($namespace),
            'text'       => $text ?? sprintf('[%s]', $name),
        ]);

        return true;
    }

    public function createPhrase(array $attributes): Phrase
    {
        $name                     = $attributes['name'];
        $group                    = $attributes['group'];
        $packageId                = $attributes['package_id'] ?? 'metafox/core';
        $namespace                = $attributes['namespace'] ?? 'core';
        $locale                   = $attributes['locale'];
        $attributes['package_id'] = $packageId;
        $attributes['namespace']  = $namespace;
        $attributes['key']        = toTranslationKey($namespace, $group, $name);

        if ($this->checkExistKey($attributes['key'], $locale)) {
            throw ValidationException::withMessages([
                'key' => __p('core::validation.the_attribute_already_existed', ['attribute' => 'key']),
            ]);
        }

        $phrase = new Phrase($attributes);
        $phrase->save();

        return $phrase;
    }

    public function checkExistKey(string $key, string $locale): bool
    {
        return $this->getModel()->newQuery()
            ->where('key', $key)
            ->where('locale', $locale)
            ->exists();
    }

    public function updatePhrase(int $id, array $attributes): Phrase
    {
        /** @var Phrase $phrase */
        $phrase = $this->find($id);

        // check value is changed.
        if ($phrase->text !== $attributes['text']) {
            $phrase->is_modified = 1;
        }

        $phrase->fill($attributes)->save();

        return $phrase;
    }

    /**
     * @inheritDoc
     */
    public function updatePhraseByKey(string $key, string $text, string $locale): ?Phrase
    {
        [$namespace, $group, $name] = app('translator')->parseKey($key);
        $package = resolve(PackageRepositoryInterface::class)
            ->where(['alias' => $namespace])->first();

        $phrase = $this->getModel()->newModelQuery()
            ->firstOrNew([
                'key'    => $key,
                'locale' => $locale,
            ], [
                'namespace'  => $namespace,
                'group'      => $group,
                'name'       => $name,
                'package_id' => $package->name,
            ]);

        // check value is changed.
        if ($phrase->text !== $text) {
            $phrase->is_modified = 1;
        }

        $phrase->fill(['text' => $text]);
        $phrase->save();

        return $phrase->refresh();
    }

    public function viewPhrases(array $attributes)
    {
        $locale = $attributes['locale'] ?? config('app.locale');
        $view   = $attributes['view'] ?? ViewScope::VIEW_DEFAULT;
        $query  = $this->getModel()->newQuery();

        if ($q = $attributes['q'] ?? null) {
            $query = $query->addScope(new SearchScope($q, ['key', 'text', 'default_text']));
        }

        if ($group = $attributes['group'] ?? null) {
            $query->where('phrases.group', '=', $group);
        }

        if ($namespace = $attributes['namespace'] ?? null) {
            $query->where('phrases.namespace', '=', $namespace);
        }

        if ($package = $attributes['package_id'] ?? null) {
            $query->where('phrases.package_id', '=', $package);
        }

        $viewScope = new ViewScope();
        $viewScope->setLocale($locale)->setView($view);

        return $query
            ->select('phrases.*')
            ->addScope($viewScope)
            ->paginate($attributes['limit'] ?? Pagination::DEFAULT_ITEM_PER_PAGE);
    }

    public function getGroupOptions(): array
    {
        /** @var Collection<Phrase> $data */
        $data = $this->getModel()->newQuery()->select('group')->distinct()->get();

        return $data->map(function (Phrase $item) {
            return ['label' => $item->group, 'value' => $item->group];
        })->toArray();
    }

    public function getLocaleOptions(): array
    {
        /** @var Collection<Phrase> $data */
        $data = $this->getModel()->newQuery()->select('locale')->distinct()->get();

        return $data->map(function ($item) {
            return ['label' => $item->locale, 'value' => $item->locale];
        })->toArray();
    }

    public function addTranslation(string $key, string $text, string $locale): void
    {
        [$namespace, $group, $name] = app('translator')->parseKey($key);
        $packageId = PackageManager::getByAlias($namespace);

        /** @var Phrase $obj */
        $obj = $this->getModel()->newQuery()
            ->firstOrNew([
                'key'    => $key,
                'locale' => $locale,
            ], [
                'namespace'  => $namespace,
                'group'      => $group,
                'name'       => $name,
                'locale'     => $locale,
                'package_id' => $packageId ? $packageId : 'metafox/core',
            ]);

        $obj->text = $text;
        $obj->save();
    }

    public function findDuplicatedPhrases(): array
    {
        $rows = DB::select(DB::raw("SELECT text, count(text) FROM phrases where text <> '' and locale='en' and package_id='metafox/blog' group by text having count(text) > 1")->getValue(DB::getQueryGrammar()));

        $array = array_map(function ($row) {
            return $row->text;
        }, $rows);

        return $array;
    }

    public function deletePhrasesByLocale(string $locale): bool
    {
        return $this->getModel()
            ->newModelQuery()
            ->where('locale', '=', $locale)
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function deletePhraseByKey(string $key): bool
    {
        $this->getModel()->newModelQuery()
            ->where('key', $key)
            ->get()
            ->collect()
            ->each(function (Phrase $phrase) {
                $phrase->delete();
            });

        return true;
    }

    /**
     * @inheritDoc
     */
    public function syncMissingPhrases(string $locale): void
    {
        // English is our master locale => no need to sync
        if ($locale === 'en') {
            return;
        }

        $upsertData = [];
        $query      = $this->getModel()
            ->newModelQuery()
            ->select(['phrases.*'])
            ->where('phrases.locale', 'en')
            ->leftJoin('phrases as derived', function (JoinClause $join) use ($locale) {
                $join->on('phrases.key', '=', 'derived.key');
                $join->where('derived.locale', $locale);
            })
            ->whereNull('derived.key');

        foreach ($query->cursor() as $phrase) {
            if (!$phrase instanceof Phrase) {
                continue;
            }

            $upsertData[] = [
                'key'        => $phrase->key,
                'name'       => $phrase->name,
                'package_id' => $phrase->package_id,
                'group'      => $phrase->group,
                'namespace'  => $phrase->namespace,
                'locale'     => $locale,
                'text'       => $phrase->text,
            ];
        }

        $this->getModel()->newModelQuery()->upsert($upsertData, ['key', 'locale'], ['updated_at']);
    }

    public function getPhrasesByKey(string $key): Collection
    {
        return $this->getModel()->newModelQuery()->where('key', $key)->get();
    }
}
