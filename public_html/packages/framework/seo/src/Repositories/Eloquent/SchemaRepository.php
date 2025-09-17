<?php

namespace MetaFox\SEO\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\SEO\Models\Meta;
use MetaFox\SEO\Models\Schema;
use MetaFox\SEO\Repositories\SchemaRepositoryInterface;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class SchemaRepository.
 */
class SchemaRepository extends AbstractRepository implements SchemaRepositoryInterface
{
    public function model()
    {
        return Schema::class;
    }

    /**
     * @param  string $package
     * @param  array  $pages
     * @return void
     */
    public function setupSEOMetaSchemas(string $package, array $pages): void
    {
        if (empty($pages)) {
            return;
        }
        $moduleId = PackageManager::getAlias($package);
        $fields   = (new Schema())->getFillable();

        $inserts = [];
        foreach ($pages as $row) {
            $metaName = Arr::pull($row, 'name');
            if (!$metaName) {
                continue;
            }

            $meta = Meta::query()
                ->where('name', $metaName)
                ->where('module_id', $moduleId)->first();
            if (!$meta instanceof Meta) {
                continue;
            }

            $structure = Arr::pull($row, 'structure', []);

            $inserts[] = Arr::only([
                'meta_id'        => $meta->entityId(),
                'schema'         => json_encode($structure),
                'schema_default' => json_encode($structure),
            ], $fields);
        }

        Schema::query()->upsert($this->excludeModified($inserts), ['meta_id'], ['schema', 'schema_default']);
        Schema::query()->upsert($this->onlyModified($inserts), ['meta_id'], ['schema_default']);
    }

    /**
     * @param        $data
     * @return array
     */
    protected function excludeModified($data): array
    {
        if (!count($data)) {
            return [];
        }

        $metaIds = array_map(function ($data) {
            return $data['meta_id'];
        }, $data);

        $metaIds = Schema::query()
            ->whereIn('meta_id', $metaIds)
            ->where('is_modified', '=', 1)
            ->pluck('meta_id')
            ->toArray();

        if (!count($metaIds)) {
            return $data;
        }

        return array_filter($data, function ($item) use ($metaIds) {
            return !in_array($item['meta_id'], $metaIds);
        });
    }

    /**
     * @param        $data
     * @return array
     */
    protected function onlyModified($data): array
    {
        if (!count($data)) {
            return [];
        }

        $metaIds = array_map(function ($data) {
            return $data['meta_id'];
        }, $data);

        $metaIds = Schema::query()
            ->whereIn('meta_id', $metaIds)
            ->where('is_modified', '=', 1)
            ->pluck('meta_id')
            ->toArray();

        if (!count($metaIds)) {
            return $data;
        }

        return array_filter($data, function ($item) use ($metaIds) {
            return in_array($item['meta_id'], $metaIds);
        });
    }

    /**
     * @inheritDoc
     */
    public function buildSEOMetaSchemas(Meta $meta, mixed $modelItem): array
    {
        $schemaModel = $meta->schema;
        if (!$schemaModel instanceof Schema) {
            return $this->mappingSchema($this->getStructuredDefault($meta), $modelItem);
        }

        $schema = $schemaModel->schema;
        if (!is_array($schema)) {
            return [];
        }

        return $this->mappingSchema($schema, $modelItem);
    }

    /**
     * @param  array $data
     * @param  mixed $modelItem
     * @return array
     */
    protected function mappingSchema(array $data, mixed $modelItem): array
    {
        $results = [];

        foreach ($data as $property => $value) {
            if (!is_array($value)) {
                preg_match($this->patternCheckValueOrRelation(), $value, $matches);

                if (count($matches) <= 2) {
                    $this->setPropertyValue($results, $property, $this->transformValue($value, $modelItem));
                    continue;
                }

                $this->setPropertyValue($results, $property, $this->buildStructured($matches, $value, $modelItem));
                continue;
            }

            collect(Arr::dot($value))->each(function ($item) use (&$matches) {
                if (is_array($item)) {
                    return null;
                }

                if (preg_match($this->patternCheckValueOrRelation(), $item, $matches)) {
                    return $item;
                }

                return null;
            })->filter()->undot();

            $this->setPropertyValue($results, $property, $this->buildStructured($matches, $value, $modelItem));
        }

        return $results;
    }

    protected function buildStructured(array $matches, mixed $data, ?Model $modelItem): mixed
    {
        if (count($matches) <= 2 && is_array($data)) {
            return $this->mappingSchema($data, $modelItem);
        }

        [, $attribute] = $matches;

        if ($modelItem?->isRelation($attribute)) {
            $modelItem->loadMissing($attribute);
            $relation = $modelItem->getRelation($attribute);

            if ($relation instanceof Collection) {
                $relation = $relation->first();
            }

            if ($relation instanceof Entity) {
                $attribute = $relation->entityType();
            }
        }

        $handler = $this->getHandler($attribute);

        return $handler?->buildStructured($data, $modelItem);
    }

    /**
     * @param  string $value
     * @param  ?Model $modelItem
     * @return mixed
     */
    public function transformValue(string $value, ?Model $modelItem): mixed
    {
        if (__is_phrase($value)) {
            return __p($value, $modelItem?->getAttributes() ?? []);
        }

        if (!$modelItem instanceof Entity) {
            return $value;
        }

        if (!preg_match($this->patternCheckValueOrRelation(), $value, $matches)) {
            return $value;
        }

        return $this->handleSingleValue($value, $matches, $modelItem);
    }

    /**
     * @param  mixed      $input
     * @param  array      $matches
     * @param  Model      $modelItem
     * @param  mixed|null $default
     * @return mixed
     */
    public function handleSingleValue(mixed $input, array $matches, Model $modelItem, mixed $default = null): mixed
    {
        if (count($matches) > 3) {
            return $default;
        }

        if (count($matches) == 3) {
            [$match, , $attributeChild] = $matches;

            $matches = [$match, $attributeChild];
        }

        $resources = $this->loadPropertiesSchema($modelItem);

        [$match, $attribute] = $matches;

        $value = Arr::get($resources, $attribute, $default);

        if (!is_array($value)) {
            return Str::replace($match, $value, $input);
        }

        $value = json_encode($value);

        return json_decode(Str::replace($match, $value, $input));
    }

    /**
     * @param  Model $model
     * @return array
     */
    public function loadPropertiesSchema(Model $model): array
    {
        $context = Auth::user() ?? UserFacade::getGuestUser();
        if (!$context->can('view', [$model])) {
            return [];
        }

        $properties         = ResourceGate::asJson($model, 'properties_schema', false) ?? [];
        $overrideProperties = app('events')->dispatch("seo.{$model->entityType()}.properties_schema", [$model]);

        if (!is_array($overrideProperties)) {
            return $properties;
        }

        foreach ($overrideProperties as $property) {
            if (is_array($property) && count($property)) {
                $properties = array_merge($properties, $property);
            }
        }

        return $properties;
    }

    public function getHandler(string $relation, ?string $resolution = null)
    {
        try {
            [, $handler] = $this->driverRepository()->loadDriver('schema-structured', $relation, $resolution);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());

            return null;
        }

        return resolve($handler);
    }

    protected function driverRepository(): DriverRepositoryInterface
    {
        return resolve(DriverRepositoryInterface::class);
    }

    public function patternCheckValueOrRelation(): string
    {
        return '/\{(?|(\w+)|(\w+).(\w+))\}/m';
    }

    public function getStructuredDefault(Meta $meta): array
    {
        $name = $meta->title;
        if (empty($name)) {
            $name = Settings::get('core.general.site_title');
        }

        return [
            '@context'        => 'http://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type'    => 'ListItem',
                    'position' => 1,
                    'item'     => [
                        '@id'  => $meta->url ? url_utility()->makeApiUrl($meta->url) : config('app.url'),
                        'name' => $name,
                    ],
                ],
            ],
        ];
    }

    private function setPropertyValue(array &$results, string $property, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        Arr::set($results, $property, $value);
    }
}
