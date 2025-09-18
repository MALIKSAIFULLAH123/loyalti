<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Profile\Support\Facade\CustomProfile;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class Setting\AdditionalInformationForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class AdditionalInformationForm extends AbstractForm
{
    public function boot(PageRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $values = CustomProfile::denormalize($this->resource, [
            'for_form'     => true,
            'section_type' => CustomField::SECTION_TYPE_PAGE,
        ]);

        $this->action("page/profile/{$this->resource->entityId()}")
            ->secondAction('page/updatePageInfo')
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        CustomFieldFacade::loadFieldsEdit($this, $this->resource, [
            'section_type' => CustomField::SECTION_TYPE_PAGE,
            'resolution'   => MetaFoxConstant::RESOLUTION_WEB,
        ]);

        $this->addFooter(['separator' => false])
            ->addFields(
                Builder::submit()
                    ->disableWhenClean()
                    ->label(__p('core::phrase.save_changes')),
                Builder::cancelButton(),
            );
    }
}
