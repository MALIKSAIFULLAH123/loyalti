<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Facades\Settings;

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
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $text = '';

        if ($this->resource->groupText instanceof ResourceText) {
            $text = parse_output()->parseItemDescription($this->resource->groupText->text_parsed, false, true);
        }

        $this->title(__p('group::phrase.label.text'))
            ->action("group/{$this->resource->entityId()}")
            ->secondAction('@updatedItem/group')
            ->asPut()
            ->setValue([
                'text_description' => $text,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addField(
                $this->buildTextField(),
            );
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text_description')
                ->label(__p('core::phrase.description'));
        }

        return Builder::textArea('text_description')
            ->label(__p('core::phrase.description'));
    }
}
