<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder as Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Contracts\HasLocationCheckin;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateLocationMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateLocationMobileForm extends AbstractForm
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

        $this->title(__p('page::phrase.label.location'))
            ->action("page/{$this->resource->entityId()}")
            ->secondAction('@updatedItem/page')
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
    }
}
