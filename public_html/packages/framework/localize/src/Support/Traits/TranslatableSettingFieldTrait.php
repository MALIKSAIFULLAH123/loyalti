<?php

namespace MetaFox\Localize\Support\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\PackageManager;

trait TranslatableSettingFieldTrait
{
    protected string $group = 'translatable';

    protected function saveTranslatableValue(array &$submitValues): void
    {
        $this->processPhrases($submitValues);

        foreach ($this->varsTranslatable as $settingName) {
            Arr::forget($submitValues, $settingName);
        }
    }

    protected function processPhrases(array $submitValues): void
    {
        $this->processCreatePhrases($submitValues);
        $this->processUpdatePhrases($submitValues);
    }

    protected function processCreatePhrases(array $submitValues): void
    {
        $phraseKeys           = [];
        $createData           = [];
        $translatableSettings = $this->getTranslatableSettings();

        foreach ($this->varsTranslatable as $settingName) {
            $setting = $translatableSettings->where('name', $settingName)->first();

            if (!$setting instanceof SiteSetting) {
                continue;
            }

            $phraseKey = $setting->getValue();

            if (__is_phrase($phraseKey)) {
                continue;
            }

            $phraseName               = $this->generatePhraseName($setting);
            $namespace                = PackageManager::getAlias($setting->package_id);
            $phraseKeys[$settingName] = toTranslationKey($namespace, $this->group, $phraseName);

            $defaultData = [
                'package_id' => $setting->package_id,
                'namespace'  => $namespace,
                'group'      => $this->group,
                'name'       => $phraseName,
            ];

            $createData = array_merge($createData, $this->buildCreateData($defaultData, Arr::get($submitValues, $settingName, [])));
        }

        if (!empty($createData)) {
            app('events')->dispatch('localize.phrase.mass_create', [$createData], true);
        }

        Settings::save($phraseKeys);
    }

    protected function processUpdatePhrases(array $submitValues): void
    {
        $updateData           = [];
        $translatableSettings = $this->getTranslatableSettings();

        foreach ($this->varsTranslatable as $settingName) {
            $setting = $translatableSettings->where('name', $settingName)->first();

            if (!$setting instanceof SiteSetting) {
                continue;
            }

            $phraseKey = $setting->getValue();

            foreach (Arr::get($submitValues, $settingName, []) as $locale => $text) {
                $updateData[] = [$phraseKey, $text ?: '', $locale];
            }
        }

        app('events')->dispatch('localize.phrase.mass_update', [$updateData], true);
    }

    protected function buildCreateData(array $defaultData, array $localeValues): array
    {
        $result = [];

        foreach ($localeValues as $locale => $text) {
            $result[] = array_merge($defaultData, [
                'locale'       => $locale,
                'text'         => $text ?: '',
                'default_text' => $text ?: '',
                'is_modified'  => 1,
            ]);
        }

        return $result;
    }

    protected function generatePhraseName(SiteSetting $setting): string
    {
        return sprintf('%s_%s_%s', $setting->entityType(), $setting->entityId(), $setting->name);
    }

    protected function getTranslatableValue(array &$data): void
    {
        $actualSettingValues = $this->getTranslatableSettings()
            ->mapWithKeys(fn ($setting) => [$setting->name => $setting->getValue()])
            ->toArray();

        foreach ($this->varsTranslatable as $settingName) {
            Arr::set($data, $settingName, Language::getPhraseValues(Arr::get($actualSettingValues, $settingName)));
        }
    }

    protected function prepareTranslatable(): void
    {
        $actualSettingValues = $this->getTranslatableSettings()
            ->mapWithKeys(fn ($setting) => [$setting->name => $setting->getValue()])
            ->toArray();

        $values = $this->getValue();

        foreach ($this->varsTranslatable as $settingName) {
            $phraseKey = Arr::get($actualSettingValues, $settingName);

            if (!is_string($phraseKey)) {
                continue;
            }

            Arr::set($values, $settingName, Language::getPhraseValues($phraseKey));
        }

        $this->setValue($values);
    }

    protected function getTranslatableSettings(): Collection
    {
        return SiteSetting::query()
            ->whereIn('name', $this->varsTranslatable)
            ->get();
    }

    /**
     * validated.
     * @param  Request      $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        $submitValues = $request->all();

        $this->saveTranslatableValue($submitValues);

        return $submitValues;
    }
}
