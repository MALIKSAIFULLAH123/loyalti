<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\GettingStarted\Support\Helper;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

class StoreTodoListForm extends AbstractForm
{
    protected bool $isEdit = false;

    public function __construct($resource = null, bool $isEdit = false)
    {
        parent::__construct($resource);

        $this->isEdit = $isEdit;
    }

    protected function prepare(): void
    {
        $this->asPost()
            ->title(__p('getting-started::phrase.add_todo_list'))
            ->action(url_utility()->makeApiUrl('admincp/getting-started/todo-list'))
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::translatableText('title')
                ->label(__p('core::phrase.title'))
                ->required()
                ->maxLength(Helper::MAX_TITLE_LENGTH)
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->maxLength(Helper::MAX_TITLE_LENGTH)
                )
                ->buildFields(),
            Builder::translatableText('text')
                ->label(__p('core::phrase.description'))
                ->required()
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                )
                ->asTextEditor()
                ->buildFields(),
            Builder::choice('resolution')
                ->label(__p('core::phrase.resolution'))
                ->required()
                ->disabled($this->getIsEdit())
                ->options([
                    [
                        'label' => __p('core::phrase.web_resolution_label'),
                        'value' => MetaFoxConstant::RESOLUTION_WEB,
                    ],
                    [
                        'label' => __p('core::phrase.mobile_resolution_label'),
                        'value' => MetaFoxConstant::RESOLUTION_MOBILE,
                    ],
                ])
                ->Yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                ),
            Builder::uploadMultiMedia('attached_photos')
                ->label(__p('core::phrase.add_photos'))
                ->placeholder(__p('core::phrase.drag_and_drop_photos_upload'))
                ->accepts('image/*')
                ->allowDrop()
                ->itemType('todo_list')
                ->uploadUrl('file')
                ->maxFiles(Helper::MAX_FILES)
                ->yup(
                    Yup::array()
                        ->nullable()
                        ->max(Helper::MAX_FILES, __p('getting-started::validation.the_number_of_files_exceeds_the_limit', ['limit' => Helper::MAX_FILES]))
                )
        );

        $this->addDefaultFooter();
    }

    protected function setIsEdit(bool $isEdit): void
    {
        $this->isEdit = $isEdit;
    }

    protected function getIsEdit(): bool
    {
        return $this->isEdit;
    }
}
