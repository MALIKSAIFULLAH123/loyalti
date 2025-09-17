<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserRelationData as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserRelationDataImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'user_type',
        'with_user_type',
        'total_like',
        'total_comment',
        'updated_at',
        'created_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'relation',
        'user',
        'withUser',
        'status',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

}
