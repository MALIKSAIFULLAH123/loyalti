<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserRelation as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserRelationImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'phrase_var',
        'confirm',
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
