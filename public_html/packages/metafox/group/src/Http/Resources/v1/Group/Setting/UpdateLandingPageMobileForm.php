<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder as Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateLandingPageForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateLandingPageMobileForm extends AbstractForm
{
    protected array $profileMenus;

    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource     = $repository->find($id);
        $this->profileMenus = $repository->getProfileMenus($this->resource->entityId());
    }

    protected function prepare(): void
    {
        $this->title(__p("group::phrase.label.landing_page"))
            ->action("group/{$this->resource->entityId()}")
            ->secondAction('@updatedItem/group')
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
                    ->options($this->profileMenus),
            );
    }
}
