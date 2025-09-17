<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor\Admin;

use Illuminate\Support\Carbon;
use MetaFox\Advertise\Http\Resources\v1\Sponsor\EditSponsorForm as MainForm;
use MetaFox\Advertise\Policies\SponsorPolicy;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Advertise\Models\Sponsor as Model;
use MetaFox\Yup\Yup;

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
class EditSponsorForm extends MainForm
{
    protected function prepare(): void
    {
        $this->title(__p('advertise::phrase.edit_sponsor'))
            ->action('admincp/advertise/sponsor/' . $this->resource->entityId())
            ->asPut()
            ->setValue([
                'title'      => $this->resource->title,
                'genders'    => $this->getEditGenders(),
                'age_from'   => $this->resource->age_from,
                'age_to'     => $this->resource->age_to,
                'languages'  => $this->getEditLanguages(),
                'location'   => $this->getLocations(),
                'start_date' => $this->resource->start_date ? Carbon::parse($this->resource->start_date)->toISOString() : null,
                'end_date'   => $this->resource->end_date ? Carbon::parse($this->resource->end_date)->toISOString() : null,
            ]);
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
    }

    protected function addStartDateField(Section $section): void
    {
        $section->addField(
            Builder::datetime('start_date')
                ->label(__p('advertise::phrase.start_date'))
                ->disabled($this->resource->is_ended)
                ->required()
                ->timeSuggestion()
                ->labelTimePicker(__p('advertise::phrase.start_time'))
                ->labelDatePicker(__p('advertise::phrase.start_date'))
                ->minDateTime(Carbon::now()->toISOString() ?? '')
                ->yup(
                    Yup::date()
                        ->required(__p('advertise::validation.start_date_is_a_required_field'))
                        ->setError('typeError', __p('advertise::validation.start_date_is_a_required_field'))
                )
        );
    }

    protected function addEndDateField(Section $section): void
    {
        $description = null;

        if ($this->resource->is_ended) {
            $description = __p('advertise::phrase.you_cant_edit_the_end_date_because_the_sponsorship_has_ended');
        }

        $section->addField(
            Builder::datetime('end_date')
                ->disabled($this->resource->is_ended)
                ->label(__p('advertise::phrase.end_date'))
                ->labelTimePicker(__p('advertise::phrase.end_time'))
                ->labelDatePicker(__p('advertise::phrase.end_date'))
                ->timeSuggestion()
                ->minDateTime(Carbon::now()->toISOString() ?? '')
                ->nullable()
                ->description($description)
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'start_date'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('event::phrase.end_date')]))
                        ->setError('min', __p('advertise::validation.the_end_date_should_be_greater_than_the_start_date'))
                ),
        );
    }
}
