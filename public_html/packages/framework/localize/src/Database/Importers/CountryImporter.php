<?php

namespace MetaFox\Localize\Database\Importers;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Localize\Models\Country as Model;

/*
 * stub: packages/database/json-importer.stub
 */

class CountryImporter extends JsonImporter
{
    // fill from data to model attributes.
    protected $fillable = [
    'country_iso',
    'code',
    'code3',
    'numeric_code',
    'geonames_code',
    'fips_code',
    'area',
    'currency',
    'phone_prefix',
    'mobile_format',
    'landline_format',
    'trunk_prefix',
    'population',
    'continent',
    'language',
    'name',
    'ordering',
    'is_active'
];

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function saving($model, array $data, array $relations, string $source):void
    {
        if (!$model instanceof Model) {
            throw new \RuntimeException(sprintf('Failed importing data %s', __CLASS__));
        }

        // handle saving logic
    }

    public function saved($model, array $data, array $relations, string $source):void
    {
        if (!$model instanceof Model) {
            throw new \RuntimeException(sprintf('Failed importing data %s', __CLASS__));
        }

        // handle saved logic
    }
}
