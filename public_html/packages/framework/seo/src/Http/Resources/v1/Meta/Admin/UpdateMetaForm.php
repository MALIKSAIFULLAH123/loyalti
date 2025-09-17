<?php

namespace MetaFox\SEO\Http\Resources\v1\Meta\Admin;

use Illuminate\Database\Eloquent\Relations\Relation;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\SEO\Models\Meta as Model;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class UpdateMetaForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateMetaForm extends StoreMetaForm
{
    public function boot($id, MetaRepositoryInterface $repository)
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit') . ' "' . $this->resource->name . '"')
            ->action(apiUrl('admin.seo.metum.update', ['metum' => $this->resource->id]))
            ->asPut()
            ->setValue([
                'title'           => $this->resource->phrase_title ? Language::getPhraseValues($this->resource->phrase_title) : '',
                'description'     => $this->resource->phrase_description ? Language::getPhraseValues($this->resource->phrase_description) : '',
                'keywords'        => $this->resource->phrase_keywords ? Language::getPhraseValues($this->resource->phrase_keywords) : '',
                'heading'         => $this->resource->phrase_heading ? Language::getPhraseValues($this->resource->phrase_heading) : '',
                'resolution'      => $this->resource->resolution,
                'canonical_url'   => $this->resource->canonical_url,
                'robots_no_index' => (int) ($this->resource->robots_no_index),
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $this->buildPropertiesSupportField($basic);

        $basic->addFields(
            Builder::translatableText('title')
                ->required()
                ->label(__p('core::phrase.title'))
                ->yup(Yup::string()->required())
                ->originalText($this->resource->phrase_title, true)
                ->buildFields(),
            Builder::translatableText('heading')
                ->optional()
                ->yup(Yup::string()->optional()->nullable())
                ->showWhen(['equals', 'resolution', 'admin'])
                ->label(__p('seo::phrase.page_heading'))
                ->buildFields(),
            Builder::translatableText('keywords')
                ->optional()
                ->showWhen(['notEquals', 'resolution', 'admin'])
                ->yup(Yup::string()->optional()->nullable())
                ->label(__p('seo::phrase.page_keywords'))
                ->buildFields(),
            Builder::translatableText('description')
                ->asTextArea()
                ->optional()
                ->label(__p('seo::phrase.page_description'))
                ->showWhen(['notEquals', 'resolution', 'admin'])
                ->yup(Yup::string()->optional()->maxLength(128))
                ->buildFields(),
            Builder::url('canonical_url')
                ->label(__p('seo::phrase.meta_canonical_url_label'))
                ->description(__p('seo::phrase.meta_canonical_url_desc'))
                ->yup(
                    Yup::string()->url(__p('seo::validation.canonical_url_must_be_valid_url'))
                ),
            Builder::switch('robots_no_index')
                ->label(__p('seo::phrase.robots_no_index_label'))
                ->description(__p('seo::phrase.robots_no_index_desc')),
        );

        $this->addDefaultFooter($this->resource?->id > 0);
    }

    protected function buildPropertiesSupportField(Section $section): void
    {
        $resourceName = $this->resource->resource_name;
        if ($resourceName === null) {
            return;
        }

        $modelName = Relation::getMorphedModel($resourceName);

        if (null === $modelName) {
            return;
        }

        $model = resolve($modelName);

        if (!$model instanceof Entity) {
            return;
        }

        $section->addFields(
            Builder::alert('properties_supported')
                ->asInfo()
                ->message(__p('seo::phrase.note_using_properties_supported', [
                    'properties' => implode(', ', $model->getFillable()),
                ])),
        );
    }
}
