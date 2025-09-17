<?php

namespace MetaFox\SEO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\SEO\Repositories\SchemaRepositoryInterface;

abstract class AbstractsSeoSchemaData
{
    /**
     * @param mixed $data
     * @param Model $model
     * @return mixed
     */
    public function buildStructured(mixed $data, ?Model $model): mixed
    {
        return $this->transformValue($data, $model);
    }

    /**
     * @param string|null $key
     * @param bool        $isRelation
     * @return array
     */
    abstract public function includesProperties(?string $key = null, bool $isRelation = false): array;

    /**
     * @param mixed $value
     * @param Model $modelItem
     * @return mixed
     */
    protected function transformValue(mixed $value, Model $modelItem): mixed
    {
        return $this->schemaRepository()->transformValue($value, $modelItem);
    }

    /**
     * @param string      $entityType
     * @param string|null $resolution
     * @return array
     */
    public function loadPropertiesSchema(string $entityType, ?string $resolution = null): array
    {
        $name = sprintf('%s.properties_schema', $entityType);

        try {
            [, $handler] = $this->driverRepository()->loadDriver('json-resource', $name, $resolution);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return [];
        }

        $handler = new $handler(null);

        if (!$handler instanceof JsonResource) {
            return [];
        }

        $properties         = $handler->toArray(request());
        $overrideProperties = app('events')->dispatch("seo.$entityType.properties_schema", [null]);

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

    protected function getPatternCheckValueOrRelation(): string
    {
        return $this->schemaRepository()->patternCheckValueOrRelation();
    }

    protected function driverRepository(): DriverRepositoryInterface
    {
        return resolve(DriverRepositoryInterface::class);
    }

    protected function schemaRepository(): SchemaRepositoryInterface
    {
        return resolve(SchemaRepositoryInterface::class);
    }
}
