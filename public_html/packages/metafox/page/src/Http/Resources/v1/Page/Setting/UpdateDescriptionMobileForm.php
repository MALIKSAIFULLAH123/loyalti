<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder as Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateDescriptionMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateDescriptionMobileForm extends AbstractForm
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

        $this->title(__p('page::phrase.label.text'))
            ->action("page/{$this->resource->entityId()}")
            ->secondAction('@updatedItem/page')
            ->asPut()
            ->setValue([
                'text' => $text,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                $this->buildTextField(),
            );
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->label(__p('core::phrase.description'))
                ->yup(Yup::string());
        }

        return Builder::textArea('text')
            ->label(__p('core::phrase.description'))
            ->yup(Yup::string());
    }
}
