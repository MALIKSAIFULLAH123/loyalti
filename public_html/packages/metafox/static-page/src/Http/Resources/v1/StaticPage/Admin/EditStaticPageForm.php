<?php

namespace MetaFox\StaticPage\Http\Resources\v1\StaticPage\Admin;

use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\StaticPage\Models\StaticPage as Model;
use MetaFox\StaticPage\Repositories\StaticPageRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditStaticPageForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditStaticPageForm extends AbstractForm
{
    public function boot(StaticPageRepositoryInterface $repository, ?int $staticPage): void
    {
        $this->resource = $repository->find($staticPage);
    }

    protected function prepare(): void
    {
        $values = [
            'title' => Language::getPhraseValues($this->resource->title_var),
            'slug'  => $this->resource?->slug,
        ];

        $contents = collect($this->resource->contents)->pluck('text', 'locale')->toArray();
        if (!empty($contents)) {
            Arr::set($values, 'text', $contents);
        }

        $this->title(__p('core::phrase.edit'))
            ->action(apiUrl('admin.static-page.page.update', ['page' => $this->resource->id]))
            ->navigationConfirmation()
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $translatableComponent =  Builder::translatableText('title')
            ->required()
            ->label(__p('core::phrase.title'))
            ->yup(Yup::string()->required())
            ->buildFields();

        $this->addBasic()
            ->addFields(
                $translatableComponent,
                Builder::slug('slug')
                    ->required()
                    ->label(__p('static-page::phrase.slug'))
                    ->mappingField($translatableComponent->defaultComponent()->getName())
                    ->separator('-')
                    ->findReplace([
                        'find'    => MetaFoxConstant::SLUGIFY_FILTERS,
                        'replace' => '-',
                    ])
                    ->sx(['mb' => 2, 'mt' => 1])
                    ->yup(
                        Yup::string()
                            ->required()
                            ->matches(MetaFoxConstant::SLUGIFY_REGEX)
                            ->setError('matches', __p('static-page::validation.slug_is_invalid'))
                    ),
                Builder::translatableText('text')
                    ->asTextEditor()
                    ->required()
                    ->label(__p('static-page::phrase.content'))
                    ->yup(Yup::string()->required())
                    ->buildFields(),
            );

        $this->addDefaultFooter();
    }
}
