<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList\Admin;

use MetaFox\Form\Builder as Builder;
use MetaFox\GettingStarted\Models\TodoList as Model;
use MetaFox\Form\AbstractForm;
use MetaFox\GettingStarted\Support\Helper;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchPointStatisticForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName gettingstarted_todolist.search.admin
 * @driverType form
 */
class SearchTodoListForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('getting-started::phrase.todo_list'))
            ->action('/getting-started/todo-list')
            ->acceptPageParams(['q', 'resolution', 'page', 'limit'])
            ->setValue([
                'q'          => '',
                'resolution' => Helper::ALL,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal();
        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm()
                ->placeholder(__p('core::phrase.search'))
                ->label(__p('core::phrase.search')),
            Builder::choice('resolution')
                ->forAdminSearchForm()
                ->placeholder(__p('core::phrase.resolution'))
                ->options([
                    ['value' => Helper::ALL, 'label' => __p('core::phrase.all')],
                    ['value' => MetaFoxConstant::RESOLUTION_WEB, 'label' => __p('core::phrase.web_resolution_label')],
                    ['value' => MetaFoxConstant::RESOLUTION_MOBILE, 'label' => __p('core::phrase.mobile_resolution_label')],
                ]),
            Builder::submit()
                ->forAdminSearchForm(),
        );
    }
}
