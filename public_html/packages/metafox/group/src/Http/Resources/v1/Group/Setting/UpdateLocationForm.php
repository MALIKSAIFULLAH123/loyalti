<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\Platform\Contracts\ResourceText;

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
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $location     = null;

        if ($this->resource instanceof HasLocationCheckin) {
            $location = $this->resource->location_name;
        }

        if (null !== $location) {
            $location = [
                'full_address' => $this->resource->location_address,
                'address' => $this->resource->location_name,
                'lat'     => $this->resource->location_latitude,
                'lng'     => $this->resource->location_longitude,
            ];
        }

        $this->action("group/{$this->resource->entityId()}")
            ->secondAction('group/updateGroupInfo')
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

        $this->addFooter(['separator' => false])
            ->addFields(
                Builder::submit()
                    ->disableWhenClean()
                    ->label(__p('core::phrase.save_changes')),
                Builder::cancelButton(),
            );
    }
}
