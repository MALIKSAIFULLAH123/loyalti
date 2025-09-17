<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
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
 * Class UpdateDescriptionForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateDescriptionForm extends AbstractForm
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

        $this->action("group/{$this->resource->entityId()}")
            ->secondAction('group/updateGroupInfo')
            ->asPut()
            ->setValue([
                'text' => $text,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addField(
                $this->buildTextDescriptionField()
            );

        $this->addFooter(['separator' => false])
            ->addFields(
                Builder::submit()
                    ->disableWhenClean()
                    ->label(__p('core::phrase.save_changes')),
                Builder::cancelButton(),
            );
    }

    protected function buildTextDescriptionField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->label(__p('core::phrase.description'));
        }

        return Builder::textArea('text')
            ->label(__p('core::phrase.description'));
    }
}
