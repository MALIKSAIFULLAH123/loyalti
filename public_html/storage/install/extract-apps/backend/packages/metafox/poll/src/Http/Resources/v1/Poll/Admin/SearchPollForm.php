<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll\Admin;

use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Html\BuiltinSearchForm;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Poll\Models\Poll as Model;
use MetaFox\Poll\Support\Browse\Scopes\Poll\ViewAdminScope;
use MetaFox\Yup\Yup;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @preload 1
 */
class SearchPollForm extends BuiltinSearchForm
{
    protected function prepare(): void
    {
        $this->action('/poll/poll/browse')
            ->acceptPageParams(['q', 'user_name', 'owner_name', 'created_from', 'view', 'created_to'])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->setValue([
                'view'         => Browse::VIEW_ALL,
                'created_from' => null,
                'created_to'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal()->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('q')
                ->label(__p('poll::phrase.poll_question'))
                ->placeholder(__p('poll::phrase.poll_question'))
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
}
