<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise;

use MetaFox\Advertise\Policies\AdvertisePolicy;
use MetaFox\Advertise\Repositories\AdvertiseRepositoryInterface;
use MetaFox\Form\Section;
use MetaFox\Advertise\Models\Advertise as Model;
use MetaFox\Advertise\Http\Resources\v1\Advertise\Admin\EditAdvertiseForm as AdminForm;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditAdvertiseForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditAdvertiseForm extends AdminForm
{
    protected function prepare(): void
    {
        $this->title(__p('advertise::phrase.edit_ad'))
            ->action('advertise/advertise/' . $this->resource->entityId())
            ->asPut()
            ->setBackProps(__p('advertise::phrase.all_ads'))
            ->setValue([
                'title'     => $this->resource->title,
                'genders'   => $this->getEditGenders(),
                'age_from'  => $this->resource->age_from,
                'age_to'    => $this->resource->age_to,
                'languages' => $this->getEditLanguages(),
                'location'  => $this->getLocations(),
            ]);
    }

    protected function addStartDateField(Section $section): void
    {
    }

    protected function addEndDateField(Section $section): void
    {
    }

    protected function addTotalFields(Section $section): void
    {
    }

    protected function addActiveField(Section $section): void
    {
    }

    protected function isAdminCP(): bool
    {
        return false;
    }

    public function boot(int $id): void
    {
        $context = user();

        $this->resource = resolve(AdvertiseRepositoryInterface::class)->find($id);

        policy_authorize(AdvertisePolicy::class, 'update', $context, $this->resource);
    }

    protected function buildDetailOnly(): bool
    {
        return true;
    }
}
