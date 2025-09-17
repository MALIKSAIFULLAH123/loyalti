<?php

namespace MetaFox\Localize\Support\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Entity;

/**
 * Prerequisite:
 *    - Model implements this trait must observe the \MetaFox\Platform\Support\EloquentModelObserver
 * Usage:
 *    - Adding a protected|public attribute $translatableAttributes in your model then it's ok.
 *    - The attribute $translatableAttributes is an array of type array<string> or array<string, string>.
 *    - For example: $translatableAttributes = ["<attribute_name>"] or ["<attribute_name>" => "<custom_phrase_key>"].
 *
 * @mixin Model
 * @mixin Entity
 *
 * @property array<string, string>|array<string> $translatableAttributes
 */
trait HasTranslatableAttributes
{
    protected string $namespace = 'localize';

    protected string $group = 'translatable';

    protected array $phraseData = [];

    /**
     * This method must be called after the record is saved.
     */
    public function createTranslatables(): void
    {
        if (!property_exists($this, 'translatableAttributes')) {
            return;
        }

        if (!$this->exists) {
            return;
        }

        $defaultData = [
            'package_id' => 'metafox/localize',
            'namespace'  => $this->namespace,
            'group'      => $this->group,
        ];

        $translatables = $this->getTranslatables();
        $phraseKeys    = $phraseData = [];

        foreach ($translatables as $attribute => $key) {
            $attributeData = Arr::get($this->phraseData, $attribute) ?? [];

            if (!is_array($attributeData) || empty($attributeData)) {
                continue;
            }

            $phraseKeys[$attribute] = $this->toTranslationKey($key);

            foreach ($attributeData as $locale => $text) {
                $phraseData[] = array_merge($defaultData, [
                    'name'         => $key,
                    'locale'       => $locale,
                    'text'         => $text,
                    'default_text' => $text,
                    'is_modified'  => 1,
                ]);
            }
        }

        if (!empty($phraseData)) {
            app('events')->dispatch('localize.phrase.mass_create', [$phraseData], true);
        }

        if (!empty($phraseKeys)) {
            $this->updateQuietly($phraseKeys);
        }
    }

    public function generateTranslatableKeys(): void
    {
        if (!property_exists($this, 'translatableAttributes')) {
            return;
        }

        $translatables = $this->getTranslatables();

        foreach ($translatables as $attribute => $key) {
            $attributeValue = Arr::get($this->attributes, $attribute);

            if (!is_array($attributeValue)) {
                continue;
            }

            $this->phraseData[$attribute] = $attributeValue;

            $this->fill([$attribute => $this->toTranslationKey($key)]);
        }
    }

    public function updateTranslatableKeys(): void
    {
        if (!property_exists($this, 'translatableAttributes')) {
            return;
        }

        $translatables = $this->getTranslatables();

        foreach ($translatables as $attribute => $key) {
            if (!$this->isDirty($attribute)) {
                continue;
            }

            $attributeValue = Arr::get($this->attributes, $attribute, []);
            if (!is_array($attributeValue)) {
                continue;
            }

            $this->phraseData[$attribute] = $attributeValue;

            $this->fill([$attribute => $this->toTranslationKey($key)]);
        }
    }

    /**
     * @param  string $name
     * @return string
     */
    public function toTranslationKey(string $name): string
    {
        return toTranslationKey($this->namespace, $this->group, $name);
    }

    public function updateTranslatables(): void
    {
        if (!property_exists($this, 'translatableAttributes')) {
            return;
        }

        $updateData    = [];
        foreach ($this->phraseData as $attribute => $phraseData) {
            $phraseVar     = Arr::get($this->attributes, $attribute, []);
            foreach ($phraseData as $locale => $text) {
                $updateData[] = [$phraseVar, $text, $locale];
            }
        }

        app('events')->dispatch('localize.phrase.mass_update', [$updateData], true);
    }

    /**
     * @return array<string>
     */
    public function getTranslatables(): array
    {
        $attributes = is_array($this->translatableAttributes) ? $this->translatableAttributes : [];

        if (count($attributes) == 0) {
            return $attributes;
        }

        $translatables = [];
        foreach ($attributes as $attribute => $key) {
            if (!is_string($key)) {
                continue;
            }

            $actualAttribute = $key;
            $phraseKey       = sprintf('%s_%s_%s', $this->entityType(), $this->entityId(), $key);

            // Support a custom phrase key defined by developer.
            // NOTE: The uniqueness of the phrase key must be manually controlled.
            if (is_string($attribute)) {
                $phraseKey       = $key;
                $actualAttribute = $attribute;
            }

            $translatables[$actualAttribute] = $phraseKey;
        }

        return $translatables;
    }

    public function deleteTranslatables(): bool
    {
        if (!property_exists($this, 'translatableAttributes')) {
            return false;
        }

        if ($this->exists) {
            return false;
        }

        $attributes = $this->translatableAttributes;

        if (!is_array($attributes)) {
            return true;
        }

        $deleteKeys = [];
        foreach ($attributes as $attribute) {
            $deleteKeys[] = $this->{$attribute};
        }

        app('events')->dispatch('localize.phrase.mass_delete', [$deleteKeys], true);

        return true;
    }
}
