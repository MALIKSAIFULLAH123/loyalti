<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Localize\Models\Phrase;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserGender as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserGenderImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'phrase',
        'is_custom',
        'updated_at',
        'created_at',
    ];

    private array $defaultGender = [
        'profile.male'   => 'user::phrase.male',
        'profile.female' => 'user::phrase.female',
    ];

    protected array $requiredColumns = ['phrase', 'name'];

    protected array $uniqueColumns = ['phrase'];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(Phrase::class, ['key', 'locale']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $isCustom = !isset($this->defaultGender[$entry['phrase']]) || $entry['is_custom'];
        $this->addEntryToBatch(Model::class, [
            'id'         => $entry['$oid'],
            'phrase'     => $this->defaultGender[$entry['phrase']] ?? 'user::' . $entry['phrase'],
            'is_custom'  => $isCustom,
            'updated_at' => $entry['updated_at'] ?? now(),
            'created_at' => $entry['created_at'] ?? now(),
        ]);
        if ($isCustom) {
            $value = explode('.', $entry['phrase']);
            $group = array_shift($value) ?? 'phrase';
            $phrase = end($value);
            $this->addEntryToBatch(Phrase::class, [
                'key'        => 'user::' . $entry['phrase'],
                'name'       => $phrase,
                'group'      => $group,
                'namespace'  => 'user',
                'package_id' => 'metafox/user',
                'locale'     => 'en',
                'text'       => $entry['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function beforePrepare(): void
    {
        foreach ($this->entries as &$entry) {
            if (isset($this->defaultGender[$entry['phrase']])) {
                $entry['phrase'] = $this->defaultGender[$entry['phrase']];
            }
        }
    }
}
