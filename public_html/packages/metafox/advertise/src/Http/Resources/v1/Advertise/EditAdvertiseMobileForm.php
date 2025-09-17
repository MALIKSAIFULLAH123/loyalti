<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Advertise\Models\Advertise as Model;
use MetaFox\Advertise\Policies\AdvertisePolicy;
use MetaFox\Advertise\Repositories\AdvertiseRepositoryInterface;
use MetaFox\Form\Section;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditAdvertiseMobileForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditAdvertiseMobileForm extends CreateAdvertiseMobileForm
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
                'age_from'  => (string)$this->resource->age_from,
                'age_to'    => (string)$this->resource->age_to,
                'languages' => $this->getEditLanguages(),
                'location'  => $this->getLocations(),
            ]);
    }

    protected function addStartDateField(Section $section): void
    {
    }

    protected function addTotalFields(Section $section): void
    {
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(?int $id = null): void
    {
        $context = user();

        $this->resource = resolve(AdvertiseRepositoryInterface::class)->find($id);

        policy_authorize(AdvertisePolicy::class, 'update', $context, $this->resource);
    }

    protected function buildDetailOnly(): bool
    {
        return true;
    }

    protected function isEdit(): bool
    {
        return true;
    }

    protected function getLocations(): ?array
    {
        $locations = $this->resource->locations()->pluck('country_code')->toArray();

        if (!count($locations)) {
            return null;
        }

        return $locations;
    }
}
