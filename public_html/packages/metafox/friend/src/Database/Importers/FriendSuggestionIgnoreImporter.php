<?php

namespace MetaFox\Friend\Database\Importers;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Friend\Models\FriendSuggestionIgnore as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class FriendSuggestionIgnoreImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [];

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
