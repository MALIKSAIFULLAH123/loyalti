<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateLocationForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateLocationForm extends AbstractForm
{
    public function boot(PageRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }
    protected function prepare(): void
    {
        $location = null;

        if ($this->resource instanceof HasLocationCheckin) {
            $location = $this->resource->location_name;
        }

        if (null !== $location) {
            $location = [
                'address' => $this->resource->location_name,
                'lat'     => $this->resource->location_latitude,
                'lng'     => $this->resource->location_longitude,
                'full_address' => $this->resource->location_address,
            ];
        }

        $this->action("page/{$this->resource->entityId()}")
            ->secondAction('page/updatePageInfo')
            ->asPut()
            ->setValue([
                'location' => $location,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::location('location')
                    ->label(__p('core::phrase.location'))
                    ->placeholder(__p('core::phrase.location'))
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
