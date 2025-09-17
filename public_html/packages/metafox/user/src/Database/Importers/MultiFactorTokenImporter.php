<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\MultiFactorToken as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class MultiFactorTokenImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'email',
        'hash_code',
        'updated_at',
        'created_at',
    ];

    // fill from data to model refs.
    protected $relations = [];

    public function getModelClass(): string
    {
        return Model::class;
    }


}
