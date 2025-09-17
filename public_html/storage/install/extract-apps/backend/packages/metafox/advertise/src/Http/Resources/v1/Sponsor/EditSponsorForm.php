<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Invoice;
use MetaFox\Advertise\Policies\SponsorPolicy;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Form\Section;
use MetaFox\Advertise\Models\Sponsor as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditSponsorForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditSponsorForm extends CreateSponsorForm
{
    protected function prepare(): void
    {
        $values = [
            'title'     => $this->resource->title,
            'genders'   => $this->getEditGenders(),
            'age_from'  => $this->resource->age_from,
            'age_to'    => $this->resource->age_to,
            'languages' => $this->getEditLanguages(),
            'location'  => $this->getLocations(),
        ];

        if ($this->isFree) {
            Arr::set($values, 'start_date', Carbon::parse($this->resource->start_date)->toISOString());

            $endDate = $this->resource->end_date;

            if (is_string($endDate)) {
                $endDate = Carbon::parse($endDate)->toISOString();
            }

            Arr::set($values, 'end_date', $endDate);
        }

        $this->title(__p('advertise::phrase.edit_sponsor'))
            ->action('advertise/sponsor/' . $this->resource->entityId())
            ->asPut()
            ->setBackProps(__p('advertise::web.sponsorships'))
            ->setValue($values);
    }

    protected function isEdit(): bool
    {
        return true;
    }

    public function boot(?int $id = null, ?string $itemType = null, ?int $itemId = null)
    {
        $this->resource = resolve(SponsorRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(SponsorPolicy::class, 'update', $context, $this->resource);

        $this->isFree = Support::isFreeSponsorInvoice($this->resource);
    }

    protected function addTotalFields(Section $section): void
    {
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
