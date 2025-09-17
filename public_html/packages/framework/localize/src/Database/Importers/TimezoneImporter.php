<?php

namespace MetaFox\Localize\Database\Importers;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Localize\Models\Timezone as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class TimezoneImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
    'code',
    'name',
    'offset',
    'diff_from_gtm',
    'is_active'
];

    public function getModelClass(): string
    {
        return Model::class;
    }
}
