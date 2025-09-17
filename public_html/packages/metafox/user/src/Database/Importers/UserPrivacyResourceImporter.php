<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserPrivacyResource as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserPrivacyResourceImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'entity_type',
        'phrase',
        'privacy_default',
    ];

    // fill from data to model refs.
    protected $relations = [
        'type',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

}
