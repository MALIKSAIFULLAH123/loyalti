<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Facade\Page;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateDescriptionForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateDescriptionForm extends AbstractForm
{
    public function boot(PageRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $text = '';

        if ($this->resource->pageText instanceof ResourceText) {
            $text = $this->resource->pageText->text_parsed;
        }

        $this->action("page/{$this->resource->entityId()}")
            ->secondAction('page/updatePageInfo')
            ->asPut()
            ->setValue([
                'text' => $text,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                $this->getDescriptionField(),
            );

        $this->addFooter(['separator' => false])
            ->addFields(
                Builder::submit()
                    ->disableWhenClean()
                    ->label(__p('core::phrase.save_changes')),
                Builder::cancelButton(),
            );
    }

    protected function getDescriptionField(): AbstractField
    {
        $field = match (Page::allowHtmlOnDescription()) {
            false   => Builder::textArea('text'),
            default => Builder::richTextEditor('text'),
        };

        return $field
            ->setAttribute('emptyValue', '')
            ->label(__p('core::phrase.description'))
            ->yup(
                Yup::string()
            );
    }
}
