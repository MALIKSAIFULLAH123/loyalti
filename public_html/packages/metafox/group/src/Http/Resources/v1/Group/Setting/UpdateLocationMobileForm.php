<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
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
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
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
                'full_address' => $this->resource->location_address,
                'lat'     => $this->resource->location_latitude,
                'lng'     => $this->resource->location_longitude,
            ];
        }

        $this->title(__p("group::phrase.label.location"))
            ->action("group/{$this->resource->entityId()}")
            ->secondAction('@updatedItem/group')
            ->asPut()
            ->setValue([
                'location' => $location,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addField(
                Builder::location('location')
                    ->label(__p('core::phrase.location'))
                    ->placeholder(__p('group::phrase.this_group_location')),
            );
    }
}
