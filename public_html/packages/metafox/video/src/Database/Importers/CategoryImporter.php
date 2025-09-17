<?php

namespace MetaFox\Video\Database\Importers;

use MetaFox\Platform\Support\JsonImporterForCategory;
use MetaFox\Video\Models\Category as Model;

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
