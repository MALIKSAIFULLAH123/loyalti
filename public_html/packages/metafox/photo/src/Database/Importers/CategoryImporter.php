<?php

namespace MetaFox\Photo\Database\Importers;

use MetaFox\Photo\Models\Category as Model;
use MetaFox\Platform\Support\JsonImporterForCategory;

/*
 * stub: packages/database/json-importer.stub
 */

class CategoryImporter extends JsonImporterForCategory
{
    public function getModelClass(): string
    {
        return Model::class;
    }
}
