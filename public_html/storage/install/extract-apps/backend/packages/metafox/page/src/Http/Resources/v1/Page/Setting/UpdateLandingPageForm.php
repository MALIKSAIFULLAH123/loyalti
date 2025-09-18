<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Form\Builder as Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateLandingPageForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateLandingPageForm extends AbstractForm
{
    protected array $profileMenus;
    public function boot(PageRepositoryInterface $repository, ?int $id): void
    {
        $this->resource     = $repository->find($id);
        $this->profileMenus = $repository->getProfileMenus($this->resource->entityId());
    }

    protected function prepare(): void
    {
        $this->action("page/{$this->resource->entityId()}")
            ->secondAction('page/updatePageInfo')
            ->asPut()
            ->setValue([
                'landing_page' => $this->resource->landing_page ?? 'home',
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addField(
                Builder::choice('landing_page')
                    ->required()
                    ->label(__p('core::phrase.landing_page'))
                    ->placeholder(__p('core::phrase.landing_page'))
                    ->options($this->profileMenus)
                    ->yup(Yup::string()->required()),
            );

        $this->addFooter(['separator' => false])
            ->addFields(
                Builder::submit()
                    ->disableWhenClean()
                    ->label(__p('core::phrase.save_changes')),
                Builder::cancelButton(),
            );
    }
}
