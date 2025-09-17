<?php

namespace MetaFox\Localize\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;

/*
 * stub: packages/database/json-importer.stub
 */

class LanguageImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'language_code',
        'name',
        'charset',
        'direction',
        'is_default',
        'is_active',
        'is_master',
        'updated_at',
        'created_at',
    ];

}
