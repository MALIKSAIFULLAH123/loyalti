<?php

namespace MetaFox\Authorization\Database\Importers;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Authorization\Models\Permission as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class PermissionImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
        'name',
        'guard_name',
        'default_value',
        'entity_type',
        'action',
        'extra',
        'data_type',
        'is_public',
        'require_admin',
        'require_staff',
        'require_user',
        'created_at',
        'updated_at',
    ];

    // fill from data to model refs.
    protected $relations = [
        'module',
    ];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function saving($model, array $data, array $relations, string $source): void
    {
        if (!$model instanceof Model) {
            throw new \RuntimeException(sprintf('Failed importing data %s', __CLASS__));
        }

        // handle saving logic
    }

    public function saved($model, array $data, array $relations, string $source): void
    {
        if (!$model instanceof Model) {
            throw new \RuntimeException(sprintf('Failed importing data %s', __CLASS__));
        }

        // handle saved logic
    }
}
