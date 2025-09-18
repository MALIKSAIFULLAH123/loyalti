<?php

namespace MetaFox\Music\Database\Importers;

use MetaFox\Music\Models\Genre as Model;
use MetaFox\Platform\Support\JsonImporterForCategory;

/*
 * stub: packages/database/json-importer.stub
 */

class GenreImporter extends JsonImporterForCategory
{
    public function getModelClass(): string
    {
        return Model::class;
    }
}
