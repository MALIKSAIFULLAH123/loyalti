<?php

namespace MetaFox\Friend\Database\Importers;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Friend\Models\FriendTagBlocked as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class FriendTagBlockedImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'user_type',
        'owner_type',
        'item_type',
        'created_at',
        'updated_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'user',
        'owner',
        'item',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }
}
