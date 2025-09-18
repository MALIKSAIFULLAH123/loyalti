<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing\Admin;

use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Html\BuiltinSearchForm;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\ViewAdminScope;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchListingForm.
 *
 * @property Model $resource
 */
class SearchListingForm extends BuiltinSearchForm
{
    protected function prepare(): void
    {
        $this->action('marketplace/marketplace/browse')
            ->acceptPageParams(['q', 'user_name', 'owner_name', 'view', 'category_id', 'created_from', 'created_to'])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->setValue([
                'view'         => ViewAdminScope::VIEW_DEFAULT,
                'created_from' => null,
                'created_to'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal()->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('q')
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('core::phrase.title'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::text('user_name')
                ->label(__p('core::phrase.posted_by'))
                ->placeholder(__p('core::phrase.posted_by'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::text('owner_name')
                ->label(__p('core::phrase.posted_to'))
                ->placeholder(__p('core::phrase.posted_to'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::choice('view')
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense()
                ->label(__p('core::phrase.view'))
                ->options(ViewAdminScope::getViewOptions()),
            Builder::category('category_id')
                ->sxFieldWrapper(['maxWidth' => 220])
                ->sizeSmall()
                ->multiple(false)
                ->marginDense()
                ->setAttribute('options', $this->getCategoryOptions()),
            Builder::date('created_from')
                ->label(__p('core::phrase.created_from'))
                ->startOfDay()
                ->forAdminSearchForm()
                ->yup(Yup::date()->nullable()
                    ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.created_from')]))),
            Builder::date('created_to')
                ->label(__p('core::phrase.created_to'))
                ->endOfDay()
                ->forAdminSearchForm()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'created_from'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.created_to')]))
                        ->setError('min', __p('validation.the_end_time_should_be_greater_than_the_start_time', [
                            'end_time'   => __p('core::phrase.created_to'),
                            'start_time' => __p('core::phrase.created_from'),
                        ]))
                ),
            Builder::submit()
                ->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->align('center')
                ->forAdminSearchForm()
                ->sizeMedium(),
        );
    }

    protected function getCategoryOptions(): array
    {
        /**@var $categoryRepository CategoryRepositoryInterface */
        $categoryRepository = resolve(CategoryRepositoryInterface::class);
        $collections        = $categoryRepository->getCategories(false);

        if ($collections->isEmpty()) {
            return [];
        }

        return $collections->toArray();
    }
}
