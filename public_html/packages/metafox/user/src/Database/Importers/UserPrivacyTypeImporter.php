<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserPrivacyType as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserPrivacyTypeImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'name',
        'privacy_default',
    ];

    // fill from data to model refs.
    protected $relations = [];

    public function getModelClass(): string
    {
        return Model::class;
    }

}
