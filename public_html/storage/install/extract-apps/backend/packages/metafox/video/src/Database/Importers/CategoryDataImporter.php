<?php

namespace MetaFox\Video\Database\Importers;

use MetaFox\Platform\Support\JsonImporterForCategoryData;
use MetaFox\Video\Models\CategoryData as Model;

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
