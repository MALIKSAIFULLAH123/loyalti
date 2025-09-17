<?php

namespace MetaFox\User\Database\Importers;

use MetaFox\User\Models\User;
use Illuminate\Support\Arr;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\UserEntity as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class UserEntityImporter extends JsonImporter
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    public function processImport()
    {
    }
}
