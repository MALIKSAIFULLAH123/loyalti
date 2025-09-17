<?php

namespace MetaFox\Blog\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Repositories\CategoryRepositoryInterface;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SiteSettingForm.
 * @property Model $resource
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'blog';
        $vars   = [
            'blog.minimum_name_length',
            'blog.maximum_name_length',
            'blog.default_category',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this
            ->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $maximumNameLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;
        $basic             = $this->addBasic();
        $categories        = $this->getCategoryRepository()->getCategoriesForForm();
        $basic->addFields(
            Builder::text('blog.minimum_name_length')
                ->required()
                ->label(__p('blog::phrase.minimum_name_length'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->unint()
                        ->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('blog.maximum_name_length')
                ->required()
                ->label(__p('blog::phrase.maximum_name_length'))
                ->maxLength($maximumNameLength)
                ->yup(
                    Yup::number()
                        ->required()
                        ->unint()
                        ->when(
                            Yup::when('minimum_name_length')
                                ->is('$exists')
                                ->then(
                                    Yup::number()
                                        ->min(['ref' => 'minimum_name_length', 1])
                                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                                )
                        )
                        ->max($maximumNameLength)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::choice('blog.default_category')
                ->label(__p('blog::phrase.blog_default_category'))
                ->required()
                ->description(__p('blog::phrase.blog_default_category_description'))
                ->disableClearable()
                ->options($categories)
                ->yup(Yup::number()),
        );

        $this->addDefaultFooter(true);
    }

    protected function getCategoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }
}
