<?php

namespace MetaFox\SEO\Repositories;

use Illuminate\Database\Eloquent\Model;
use MetaFox\SEO\Models\Meta;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Schema
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface SchemaRepositoryInterface
{
    /**
     * @param string $package
     * @param array  $pages
     * @return void
     */
    public function setupSEOMetaSchemas(string $package, array $pages): void;

    /**
     * @param Meta  $meta
     * @param mixed $modelItem
     * @return array
     */
    public function buildSEOMetaSchemas(Meta $meta, mixed $modelItem): array;

    /**
     * @param string $value
     * @param ?Model $modelItem
     * @return mixed
     */
    public function transformValue(string $value, ?Model $modelItem): mixed;

    /**
     * @param mixed      $input
     * @param array      $matches
     * @param Model      $modelItem
     * @param mixed|null $default
     * @return mixed
     */
    public function handleSingleValue(mixed $input, array $matches, Model $modelItem, mixed $default = null): mixed;

    /**
     * @param Model $model
     * @return array
     */
    public function loadPropertiesSchema(Model $model): array;

    /**
     * @return string
     */
    public function patternCheckValueOrRelation(): string;

    /**
     * @param string      $relation
     * @param string|null $resolution
     * @return mixed
     */
    public function getHandler(string $relation, ?string $resolution = null);

    /**
     * @param Meta $meta
     * @return array
     */
    public function getStructuredDefault(Meta $meta): array;
}
