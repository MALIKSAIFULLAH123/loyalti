<?php

namespace MetaFox\StaticPage\Http\Resources\v1\StaticPage\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\StaticPage\Models\StaticPage as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateStaticPageForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateStaticPageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('static-page::phrase.create_page'))
            ->action(apiUrl('admin.static-page.page.store'))
            ->navigationConfirmation()
            ->asPost()
            ->setValue([
            ]);
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
