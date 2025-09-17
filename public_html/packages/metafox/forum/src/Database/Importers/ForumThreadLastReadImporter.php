<?php

namespace MetaFox\Forum\Database\Importers;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Forum\Models\ForumThreadLastRead as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class ForumThreadLastReadImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'user_type',
        'created_at',
        'updated_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'user',
        'thread',
        'post',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

}
