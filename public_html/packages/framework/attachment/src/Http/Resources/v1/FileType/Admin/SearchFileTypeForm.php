<?php

namespace MetaFox\Attachment\Http\Resources\v1\FileType\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Core\Models\AttachmentFileType as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchFileTypeForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchFileTypeForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action(apiUrl('admin.attachment.type.index'))
            ->acceptPageParams(['q', 'is_active'])
            ->asGet()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::choice('is_active')
                    ->label(__p('core::phrase.status'))
                    ->forAdminSearchForm()
                    ->options($this->getActiveOptions()),
                Builder::submit()->forAdminSearchForm(),
            );
    }

    /**
     * @return array<int, mixed>
     */
    protected function getActiveOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.is_active'),
                'value' => 1,
            ],
            [
                'label' => __p('core::phrase.inactive'),
                'value' => 0,
            ],
        ];
    }
}
