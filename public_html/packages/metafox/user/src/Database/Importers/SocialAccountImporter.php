<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\SocialAccount as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class SocialAccountImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'provider',
        'created_at',
        'updated_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'providerUser',
        'user',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }


}
