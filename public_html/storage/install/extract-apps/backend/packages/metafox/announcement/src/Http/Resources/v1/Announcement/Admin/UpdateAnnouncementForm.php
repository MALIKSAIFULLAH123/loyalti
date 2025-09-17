<?php

namespace MetaFox\Announcement\Http\Resources\v1\Announcement\Admin;

use Illuminate\Support\Carbon;
use MetaFox\Announcement\Models\Announcement as Model;
use MetaFox\Announcement\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Core\Support\Facades\Language;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateAnnouncementForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverType form
 * @driverName announcement.update
 */
class UpdateAnnouncementForm extends StoreAnnouncementForm
{
    public function boot(AnnouncementRepositoryInterface $repository, ?int $announcement): void
    {
        $this->resource = $repository->with(['roles', 'contents'])->find($announcement);
    }

    protected function prepare(): void
    {
        $roles    = $this->resource->roles;
        $contents = collect($this->resource->contents)->pluck('text_parsed', 'locale')->toArray();

        $this->title(__p('announcement::phrase.edit_announcement'))
            ->action('/admincp/announcement/announcement/' . $this->resource->entityId())
            ->asPut()
            ->setValue([
                'subject_var'   => Language::getPhraseValues($this->resource->subject_var),
                'intro_var'     => Language::getPhraseValues($this->resource->intro_var),
                'text'          => !empty($contents) ? $contents : Language::getPhraseValues(''),
                'is_active'     => $this->resource->is_active,
                'can_be_closed' => $this->resource->can_be_closed,
                'style'         => $this->resource->style?->entityId() ?? 0,
                'start_date'    => Carbon::make($this->resource->start_date)?->toISOString(),
                'roles'         => collect($roles)->pluck('id')->toArray(),
                'countries'     => $this->resource->countries()->pluck('announcement_country_data.country_iso')->toArray(),
                'genders'       => $this->resource->genders()->pluck('gender_id')->toArray(),
            ]);
    }
}
