<?php

namespace MetaFox\SEO\Http\Resources\v1\Meta\Admin;

use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\SEO\AbstractsSeoSchemaData;
use MetaFox\SEO\Models\Meta as Model;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;
use MetaFox\SEO\Repositories\SchemaRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class UpdateSchemaMetaForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateSchemaMetaForm extends StoreMetaForm
{
    public function boot($id, MetaRepositoryInterface $repository)
    {
        $this->resource  = $repository->find($id);
        $schema          = $this->resource?->schema;
        $this->structure = $schema?->schema ?? $this->schemaRepository()->getStructuredDefault($this->resource);
    }

    protected array $structure = [];

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit') . ' "' . $this->resource->name . '"')
            ->action(apiUrl('admin.seo.meta.update.schema', ['id' => $this->resource->id]))
            ->asPut()
            ->setValue([
                'schema' => json_encode($this->structure),
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::textArea('schema')
                ->label(__p('seo::phrase.structured_data'))
                ->yup(Yup::string()->nullable()),
            Builder::alert('original_structure')
                ->asInfo()
                ->message(__p('seo::phrase.original_structure', [
                    'original_structure' => json_encode($this->structure),
                ])),
        );

        $this->buildPropertiesSupportField($basic);

        $this->addDefaultFooter($this->resource?->id > 0);
    }

    protected function includesPropertiesSupportSchema(): array
    {
        $resourceName = $this->resource->resource_name;
        if ($resourceName === null) {
            return [];
        }

        $handler = $this->schemaRepository()->getHandler($resourceName);

        if (!$handler instanceof AbstractsSeoSchemaData) {
            return [];
        }

        $includes     = $handler?->includesProperties(null, true) ?? [];
        $moreIncludes = app('events')->dispatch("seo.$resourceName.includes_properties_schema");

        foreach ($moreIncludes as $include) {
            if (is_array($include)) {
                $includes = array_merge($includes, $include);
            }
        }

        return $includes;
    }

    protected function buildPropertiesSupportField(Section $section): void
    {
        if (empty($this->includesPropertiesSupportSchema())) {
            return;
        }

        $section->addFields(
            Builder::alert('properties_supported')
                ->asInfo()
                ->message(__p('seo::phrase.properties_supported', [
                    'properties' => implode(', ', $this->includesPropertiesSupportSchema()),
                ])),
        );
    }

    protected function schemaRepository(): SchemaRepositoryInterface
    {
        return resolve(SchemaRepositoryInterface::class);
    }
}
