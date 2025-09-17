<?php

namespace MetaFox\Announcement\Http\Resources\v1\Announcement\Admin;

use Illuminate\Support\Carbon;
use MetaFox\Announcement\Models\Announcement as Model;
use MetaFox\Announcement\Support\Facade\Announcement;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreAnnouncementForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverType form
 * @driverName announcement.store
 */
class StoreAnnouncementForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('announcement::phrase.add_an_announcement'))
            ->action('/admincp/announcement/announcement')
            ->asPost()
            ->setValue([
                'subject'       => '',
                'intro'         => '',
                'is_active'     => 1,
                'can_be_closed' => 1,
                'style'         => 0,
                'start_date'    => Carbon::now()->toISOString(),
                'roles'         => [],
                'country_iso'   => '',
                'gender'        => 0,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->label(__p('announcement::phrase.announcement_content'));

        $basic->addFields(
            Builder::translatableText('subject_var')
                ->required()
                ->sx(['mb' => 2, 'mt' => 1])
                ->label(__p('announcement::phrase.announcement_subject'))
                ->buildFields(),
            Builder::translatableText('intro_var')
                ->label(__p('announcement::phrase.announcement_intro'))
                ->buildFields(),
            Builder::translatableText('text')
                ->asTextEditor()
                ->label(__p('announcement::phrase.announcement_text'))
                ->buildFields(),
        );

        $displayOptions = $this->addSection(['name' => 'displayOptions'])
            ->label(__p('announcement::phrase.display_options'));
        $displayOptions->addFields(
            Builder::checkbox('is_active')->label(__p('core::phrase.is_active')),
            Builder::checkbox('can_be_closed')->label(__p('announcement::phrase.can_be_closed')),
            Builder::choice('style')
                ->required()
                ->label(__p('announcement::phrase.announcement_style'))
                ->options(Announcement::getStyleOptions()),
            Builder::datetime('start_date')
                ->required()
                ->label(__p('announcement::phrase.start_date'))
                ->labelDatePicker(__p('announcement::phrase.start_date'))
                ->labelTimePicker(__p('announcement::phrase.start_time'))
                ->timeSuggestion()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->required(__p('announcement::validation.start_date_is_a_required_field'))
                        ->setError('typeError', __p('validation.this_field_is_not_a_valid_data'))
                ),
        );

        $targetViewers = $this->addSection(['name' => 'targetViewers'])
            ->label(__p('announcement::phrase.target_viewers'));
        $targetViewers->addFields(
            Builder::choice('roles')
                ->multiple(true)
                ->disableClearable()
                ->label(__p('core::phrase.role'))
                ->options(Announcement::getAllowedRoleOptions()),
            Builder::choice('countries')
                ->multiple(true)
                ->label(__p('announcement::phrase.locations'))
                ->options(Country::buildCountrySearchForm()),
            Builder::gender()
                ->name('genders')
                ->label(__p('user::phrase.genders'))
                ->multiple(true)
                ->options($this->getGenderOptions())
                ->disableClearable(),
        );

        $this->addDefaultFooter($this->resource?->entityId() > 0);
    }

    protected function getGenderOptions(): array
    {
        return resolve(UserGenderRepositoryInterface::class)->getGenderOptions();
    }
}
