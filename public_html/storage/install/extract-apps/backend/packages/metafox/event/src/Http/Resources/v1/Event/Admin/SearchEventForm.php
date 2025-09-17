<?php

namespace MetaFox\Event\Http\Resources\v1\Event\Admin;

use MetaFox\Event\Repositories\CategoryRepositoryInterface;
use MetaFox\Event\Support\Browse\Scopes\Event\ViewAdminScope;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchEventForm.
 * @ignore
 */
class SearchEventForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('event/event/browse')
            ->acceptPageParams(['q', 'user_name', 'owner_name', 'view', 'category_id', 'start_time', 'end_time'])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->setValue([
                'view'       => Browse::VIEW_ALL,
                'start_time' => null,
                'end_time'   => null,
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
            Builder::date('start_time')
                ->label(__p('event::phrase.start_date'))
                ->startOfDay()
                ->forAdminSearchForm()
                ->yup(Yup::date()->nullable()
                    ->setError('typeError', __p('validation.date', ['attribute' => __p('event::phrase.start_date')]))),
            Builder::date('end_time')
                ->label(__p('event::phrase.end_date'))
                ->endOfDay()
                ->forAdminSearchForm()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'start_time'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('event::phrase.start_date')]))
                        ->setError('min', __p('event::phrase.the_end_time_should_be_greater_than_the_start_time'))
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
