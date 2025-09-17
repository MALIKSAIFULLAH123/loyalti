<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\CancelReason as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class CancelReasonImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'owner_type',
        'phrase_var',
        'is_active',
        'ordering',
        'updated_at',
        'created_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'user',
        'owner',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }


}
