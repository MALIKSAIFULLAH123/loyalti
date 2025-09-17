<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserPromotion as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserPromotionImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'is_active',
        'total_activity',
        'total_day',
        'updated_at',
        'created_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'userGroup',
        'upgradeUserGroup',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }
}
