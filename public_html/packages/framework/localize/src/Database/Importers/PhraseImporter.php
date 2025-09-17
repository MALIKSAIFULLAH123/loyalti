<?php

namespace MetaFox\Localize\Database\Importers;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Localize\Models\Phrase as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PhraseImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
    'locale',
    'key',
    'namespace',
    'group',
    'text',
    'name',
    'updated_at',
    'created_at'
];

    public function getModelClass(): string
    {
        return Model::class;
    }
}
