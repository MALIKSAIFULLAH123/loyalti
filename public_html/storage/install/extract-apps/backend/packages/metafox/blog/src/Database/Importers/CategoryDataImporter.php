<?php

namespace MetaFox\Blog\Database\Importers;

use MetaFox\Blog\Models\CategoryData as Model;
use MetaFox\Platform\Support\JsonImporterForCategoryData;

/*
 * stub: packages/database/json-importer.stub
 */

class CategoryDataImporter extends JsonImporterForCategoryData
{
    public function getModelClass(): string
    {
        return Model::class;
    }
}
