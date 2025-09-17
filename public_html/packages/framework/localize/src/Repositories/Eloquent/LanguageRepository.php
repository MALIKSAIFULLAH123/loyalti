<?php

namespace MetaFox\Localize\Repositories\Eloquent;

use Illuminate\Support\Collection;
use MetaFox\Core\Support\Facades\Language as LanguageFacade;
use MetaFox\Localize\Models\Language;
use MetaFox\Localize\Repositories\LanguageRepositoryInterface;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\User;

/**
 * @method Language getModel()
 * @method Language find($id, $columns = ['*'])
 */
class LanguageRepository extends AbstractRepository implements LanguageRepositoryInterface
{
    public function model(): string
    {
        return Language::class;
    }

    public function getOptions(bool $active = null): array
    {
        $params = [];

        if (null !== $active) {
            $params['is_active'] = $active ? 1 : 0;
        }

        $localeDefault = LanguageFacade::getDefaultLocaleId();
        return $this->getModel()->newQuery()
            ->where($params)
            ->orderByRaw("CASE WHEN language_code = '$localeDefault' THEN 1 ELSE 0 END DESC")
            ->get()
            ->pluck('name', 'language_code')
            ->toArray();
    }

    public function getActiveLanguages(): Collection
    {
        $localeDefault = LanguageFacade::getDefaultLocaleId();
        return $this->getModel()->newQuery()
            ->where('is_active', '=', 1)
            ->orderByRaw("CASE WHEN language_code = '$localeDefault' THEN 1 ELSE 0 END DESC")
            ->get();
    }

    public function updateActive(User $context, int $id, bool $isActive): bool
    {
        /** @var Language $model */
        $model = $this->find($id);

        $model->is_active = $isActive;
        $model->save();

        return true;
    }

    public function getDefaultLanguage(): ?Language
    {
        /** @var Language $language */
        $language = $this->getModel()->newQuery()
            ->where('is_active', '=', 1)
            ->where('is_default', '=', 1)
            ->first();

        return $language;
    }

    public function deleteLanguage(User $context, int $id): bool
    {
        $language = $this->find($id);

        if ($language->is_master) {
            abort(401, __p('localize::phrase.cannot_remove_master_language'));
        }

        if ($language->is_default) {
            abort(401, __p('localize::phrase.cannot_remove_default_language'));
        }

        if ($language->is_active) {
            abort(401, __p('localize::phrase.cannot_remove_active_language'));
        }

        return (bool) $language->delete();
    }

    public function viewAllLanguages(array $codes = []): Collection
    {
        $query = Language::query();

        if (count($codes)) {
            $query->whereIn('language_code', $codes);
        }

        return $query->get();
    }

    /**
     * @inheritDoc
     */
    public function addMissPhrases(int $id): Language
    {
        $language = $this->find($id);
        $locale   = $language->language_code;

        if ('en' === $locale) {
            return $language;
        }

        $this->phraseRepository()->syncMissingPhrases($locale);

        return $language;
    }

    protected function phraseRepository(): PhraseRepositoryInterface
    {
        return resolve(PhraseRepositoryInterface::class);
    }
}
