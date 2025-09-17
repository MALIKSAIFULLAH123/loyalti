<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise\Admin;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Support\Support;
use MetaFox\Form\Section;
use MetaFox\Form\Builder as Builder;
use MetaFox\Advertise\Models\Advertise as Model;

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
class EditAdvertiseForm extends CreateAdvertiseForm
{
    protected function prepare(): void
    {
        $values = [
            'placement_id'     => $this->resource->placement_id,
            'url'              => $this->resource->url,
            'title'            => $this->resource->title,
            'genders'          => $this->getEditGenders(),
            'age_from'         => $this->resource->age_from,
            'age_to'           => $this->resource->age_to,
            'languages'        => $this->getEditLanguages(),
            'start_date'       => $this->getFormatDate($this->resource->start_date),
            'end_date'         => $this->getFormatDate($this->resource->end_date),
            'is_active'        => (int) $this->resource->is_active,
            'total_click'      => $this->resource->advertise_type == Support::PLACEMENT_PPC ? $this->resource->total_click : 0,
            'total_impression' => $this->resource->advertise_type == Support::PLACEMENT_CPM ? $this->resource->total_impression : 0,
            'creation_type'    => $this->resource->creation_type,
            'image'            => [
                'id' => $this->resource->advertise_file_id,
            ],
            'location' => $this->getLocations(),
        ];

        if ($this->resource->advertise_type == Support::PLACEMENT_PPC) {
            Arr::set($values, 'total_click', $this->resource->total_click);
        }

        if (is_array($this->resource->html_values)) {
            $values = array_merge($values, $this->resource->html_values);
        }

        if (is_array($this->resource->image_values)) {
            $values = array_merge($values, $this->resource->image_values);
        }

        $this->title(__p('advertise::phrase.edit_advertise'))
            ->action('admincp/advertise/advertise/' . $this->resource->entityId())
            ->setBackProps(__p('advertise::phrase.all_ads'))
            ->asPut()
            ->setValue($values);
    }

    protected function getLocations(): ?array
    {
        $locations = $this->resource->locations()->pluck('country_code')->toArray();

        if (!count($locations)) {
            return null;
        }

        return $locations;
    }

    protected function getFormatDate(?string $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return Carbon::parse($date)->format('c');
    }

    protected function addCreationTypeField(Section $section): void
    {
        $section->addFields(
            Builder::typography('creation_type_description')
                ->plainText(__p('advertise::phrase.advertise_type_description', ['type' => $this->resource->creation_type_label]))
        );
    }

    protected function isEdit(): bool
    {
        return true;
    }
}
