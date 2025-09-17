<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder as Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateLandingPageMobileForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateLandingPageMobileForm extends AbstractForm
{
    protected array $profileMenus;

    public function boot(PageRepositoryInterface $repository, ?int $id): void
    {
        $this->resource     = $repository->find($id);
        $this->profileMenus = $repository->getProfileMenus($this->resource->entityId());
    }

    protected function prepare(): void
    {
        $this->title(__p("page::phrase.label.landing_page"))
            ->action("page/{$this->resource->entityId()}")
            ->secondAction('@updatedItem/page')
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
    }
}
