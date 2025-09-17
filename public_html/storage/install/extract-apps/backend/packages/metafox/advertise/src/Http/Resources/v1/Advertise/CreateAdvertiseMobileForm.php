<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Http\Resources\v1\Advertise\Admin\CreateAdvertiseForm as AdminForm;
use MetaFox\Advertise\Models\Advertise as Model;
use MetaFox\Advertise\Policies\AdvertisePolicy;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Form\Html\AdvertiseCalculatorCost;
use MetaFox\Advertise\Support\Support;
use MetaFox\Advertise\Traits\HasGenderTrait;
use MetaFox\Advertise\Traits\HasLanguageTrait;
use MetaFox\Core\Support\Facades\Country as CountryFacade;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateAdvertiseMobileForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateAdvertiseMobileForm extends AdminForm
{
    use HasGenderTrait;
    use HasLanguageTrait;

    protected function prepare(): void
    {
        $this->action('advertise/advertise')
            ->title(__p('advertise::phrase.create_new_ad'))
            ->asPost()
            ->setBackProps(__p('advertise::phrase.all_ads'))
            ->setValue([
                'creation_type' => Support::ADVERTISE_IMAGE,
                'is_active'     => 1,
                'start_date'    => Carbon::now()->toISOString(),
                'end_date'      => null,
            ]);
    }

    protected function initialize(): void
    {
        if (!$this->isEdit() && !count($this->availablePlacements)) {
            $this->addBasic()
                ->addFields(
                    Builder::typography('no_placements')
                        ->plainText(__p('advertise::phrase.no_placements_available'))
                );

            return;
        }

        if (!$this->buildDetailOnly()) {
            $this->addGeneralSection();
        }

        $this->addDetailSection();
    }

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(?int $id = null): void
    {
        $context = user();

        policy_authorize(AdvertisePolicy::class, 'create', $context);
    }

    protected function addTotalFields(Section $section): void
    {
        $clickPlacements      = array_combine(array_column($this->availableClickPlacements, 'value'), $this->availableClickPlacements);
        $impressionPlacements = array_combine(array_column($this->availableImpressionPlacements, 'value'), $this->availableImpressionPlacements);

        $section->addFields(
            (new AdvertiseCalculatorCost())
                ->name('total_click')
                ->relatedInitialPrice('placement_id')
                ->placementOptions($clickPlacements)
                ->requiredWhen([
                    'includes',
                    'placement_id',
                    $this->availableClickPlacementIds,
                ])
                ->showWhen([
                    'includes',
                    'placement_id',
                    $this->availableClickPlacementIds,
                ])
                ->label(__p('advertise::phrase.total_clicks'))
                ->placeholder(__p('advertise::phrase.total_clicks'))
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('placement_id')
                                ->is(
                                    Yup::number()
                                        ->oneOf($this->availableClickPlacementIds)
                                        ->toArray()
                                )
                                ->then(
                                    Yup::number()
                                        ->required(__p('advertise::validation.total_clicks_is_a_required_field'))
                                        ->min(1, __p('advertise::validation.total_clicks_must_be_greater_than_or_equal_to_number', ['number' => 1]))
                                        ->max(Support::MAX_TOTAL_NUMBER, __p('advertise::validation.total_clicks_must_be_less_than_or_equal_to_number', ['number' => number_format(Support::MAX_TOTAL_NUMBER)]))
                                        ->unint(__p('advertise::validation.total_clicks_must_be_number'))
                                        ->setError('typeError', __p('advertise::validation.total_clicks_must_be_number'))
                                )
                        ),
                ),
            (new AdvertiseCalculatorCost())
                ->name('total_impression')
                ->relatedInitialPrice('placement_id')
                ->initialUnit(1000)
                ->setAttribute('relatedPlacementType', Support::PLACEMENT_CPM)
                ->placementOptions($impressionPlacements)
                ->requiredWhen([
                    'includes',
                    'placement_id',
                    $this->availableImpressionPlacementIds,
                ])
                ->showWhen([
                    'includes',
                    'placement_id',
                    $this->availableImpressionPlacementIds,
                ])
                ->label(__p('advertise::phrase.total_impressions'))
                ->placeholder(__p('advertise::phrase.total_impressions'))
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('placement_id')
                                ->is(
                                    Yup::number()
                                        ->oneOf($this->availableImpressionPlacementIds)
                                        ->toArray()
                                )
                                ->then(
                                    Yup::number()
                                        ->required(__p('advertise::validation.total_impressions_is_a_required_field'))
                                        ->min(100, __p('advertise::validation.total_impressions_must_be_greater_than_or_equal_to_number', ['number' => 100]))
                                        ->max(Support::MAX_TOTAL_NUMBER, __p('advertise::validation.total_impressions_must_be_less_than_or_equal_to_number', ['number' => number_format(Support::MAX_TOTAL_NUMBER)]))
                                        ->unint(__p('advertise::validation.total_impressions_must_be_number'))
                                        ->setError('typeError', __p('advertise::validation.total_impressions_must_be_number'))
                                )
                        ),
                )
        );
    }

    protected function addCreationTypeField(Section $section): void
    {
        $section->addFields(
            Builder::choice('creation_type')
                ->label(__p('advertise::phrase.advertise_type'))
                ->placeholder(__p('advertise::phrase.advertise_type'))
                ->options($this->getAdvertiseTypeOptions())
                ->required()
                ->yup(
                    Yup::string()
                        ->required(__p('advertise::validation.advertise_type_is_a_required_field'))
                )
        );
    }

    protected function addGeneralSection(): void
    {
        $section = $this->addSection('general')
            ->label(__p('advertise::phrase.general'));

        $this->addCreationTypeField($section);

        $section->addFields(
            Builder::choice('placement_id')
                ->label(__p('advertise::phrase.placement'))
                ->placeholder(__p('advertise::phrase.placement'))
                ->sxFieldWrapper([
                    'mb' => 0,
                ])
                ->required()
                ->options($this->availablePlacements)
                ->yup(
                    Yup::number()
                        ->required(__p('advertise::validation.placement_is_a_required_field'))
                ),
            Builder::dynamicTypography('placement_description')
                ->relatedField('placement_id')
                ->data($this->availablePlacementDescriptions),
            Builder::singlePhoto('image')
                ->required()
                ->label(__p('advertise::phrase.image'))
                ->placeholder(__p('advertise::phrase.image'))
                ->itemType('advertise')
                ->thumbnailSizes($this->getThumbnailSizes())
                ->previewUrl($this->resource?->image)
                ->description(__p('advertise::phrase.recommendation_dimention_for_images'))
                ->yup(
                    Yup::object()
                        ->nullable()
                        ->required()
                ),
            Builder::text('url')
                ->label(__p('advertise::phrase.destination_url'))
                ->placeholder(__p('advertise::phrase.destination_url'))
                ->required()
                ->yup(
                    Yup::string()
                        ->url()
                        ->required(__p('advertise::validation.destination_url_is_a_required_field'))
                ),
        );

        $this->buildFieldsForImage($section);

        $this->buildFieldsForHTML($section);
    }

    protected function getAdvertiseTypeOptions(): array
    {
        return Facade::getAdvertiseTypes();
    }

    protected function buildFieldsForHTML(Section $section): void
    {
        $section->addFields(
            Builder::text('html_title')
                ->label(__p('advertise::phrase.html_title'))
                ->placeholder(__p('advertise::phrase.html_title'))
                ->maxLength(Support::MAX_HTML_TITLE_LENGTH)
                ->requiredWhen([
                    'eq',
                    'creation_type',
                    Support::ADVERTISE_HTML,
                ])
                ->showWhen([
                    'eq',
                    'creation_type',
                    Support::ADVERTISE_HTML,
                ])
                ->yup(
                    Yup::string()
                        ->when(
                            Yup::when('creation_type')
                                ->is(Support::ADVERTISE_HTML)
                                ->then(
                                    Yup::string()
                                        ->required(__p('advertise::validation.html_title_is_a_required_field'))
                                        ->maxLength(Support::MAX_HTML_TITLE_LENGTH, __p('advertise::validation.maximum_html_title_length_is_number', ['number' => Support::MAX_HTML_TITLE_LENGTH]))
                                )
                        )
                        ->maxLength(Support::MAX_HTML_TITLE_LENGTH, __p('advertise::validation.maximum_html_title_length_is_number', ['number' => Support::MAX_HTML_TITLE_LENGTH]))
                ),
            Builder::textArea('html_description')
                ->label(__p('advertise::phrase.html_description'))
                ->placeholder(__p('advertise::phrase.html_description'))
                ->maxLength(Support::MAX_HTML_DESCRIPTION_LENGTH)
                ->requiredWhen([
                    'eq',
                    'creation_type',
                    Support::ADVERTISE_HTML,
                ])
                ->showWhen([
                    'eq',
                    'creation_type',
                    Support::ADVERTISE_HTML,
                ])
                ->yup(
                    Yup::string()
                        ->when(
                            Yup::when('creation_type')
                                ->is(Support::ADVERTISE_HTML)
                                ->then(
                                    Yup::string()
                                        ->required(__p('advertise::validation.html_description_is_a_required_field'))
                                        ->maxLength(Support::MAX_HTML_DESCRIPTION_LENGTH, __p('advertise::validation.maximum_html_description_length_is_number', ['number' => Support::MAX_HTML_DESCRIPTION_LENGTH]))
                                )
                        )
                )
        );
    }

    protected function buildFieldsForImage(Section $section): void
    {
        $section->addFields(
            Builder::text('image_tooltip')
                ->label(__p('advertise::phrase.image_tooltip'))
                ->placeholder(__p('advertise::phrase.image_tooltip'))
                ->description(__p('advertise::phrase.image_tooltip_description'))
                ->maxLength(255)
                ->showWhen([
                    'eq',
                    'creation_type',
                    Support::ADVERTISE_IMAGE,
                ]),
        );
    }

    protected function addDetailSection(): void
    {
        $section = $this->addSection('detail')
            ->label($this->buildDetailOnly() ? null : __p('advertise::phrase.detail'));

        $section->addFields(
            Builder::text('title')
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('core::phrase.title'))
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                ->required()
                ->yup(
                    Yup::string()
                        ->required(__p('core::phrase.title_is_a_required_field'))
                        ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH, __p('advertise::validation.maximum_title_length_is_number', [
                            'number' => MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH,
                        ]))
                ),
        );

        $this->addTotalFields($section);

        $section->addFields(
            Builder::choice('genders')
                ->label(__p('user::phrase.genders'))
                ->placeholder(__p('user::phrase.genders'))
                ->description(__p('advertise::phrase.no_particular_choices_mean_all_users_can_view'))
                ->multiple()
                ->options($this->getGenderOptions()),
            $this->addLocationField(),
            Builder::text('age_from')
                ->label(__p('advertise::phrase.age_from'))
                ->placeholder(__p('advertise::phrase.age_from'))
                ->asNumber()
                ->yup(
                    Yup::number()
                        ->nullable()
                        ->unint(__p('advertise::validation.age_from_must_be_integer'))
                        ->min(1, __p('advertise::validation.age_from_must_be_greater_than_or_equal_to_number', ['number' => 1]))
                        ->setError('typeError', __p('advertise::validation.age_from_must_be_integer'))
                ),
            Builder::text('age_to')
                ->label(__p('advertise::phrase.age_to'))
                ->placeholder(__p('advertise::phrase.age_to'))
                ->asNumber()
                ->showWhen([
                    'truthy',
                    'age_from',
                ])
                ->yup(
                    Yup::number()
                        ->nullable()
                        ->when(
                            Yup::when('age_from')
                                ->is('$exists')
                                ->then(
                                    Yup::number()
                                        ->nullable()
                                        ->unint(__p('advertise::validation.age_to_must_be_integer'))
                                        ->min(['ref' => 'age_from'])
                                        ->setError('typeError', __p('advertise::validation.age_to_must_be_integer'))
                                )
                        )
                        ->setError('typeError', __p('advertise::validation.age_to_must_be_integer'))
                ),
            Builder::choice('languages')
                ->label(__p('core::phrase.languages'))
                ->placeholder(__p('core::phrase.languages'))
                ->description(__p('advertise::phrase.no_particular_choices_mean_all_users_can_view'))
                ->multiple()
                ->options($this->getLanguageOptions()),
        );

        $this->addStartDateField($section);
    }

    protected function addLocationField(): ?AbstractField
    {
        if (!Settings::get('advertise.enable_advanced_filter', false)) {
            return null;
        }

        return Builder::choice('location')
            ->multiple()
            ->label(__p('core::phrase.location'))
            ->placeholder(__p('core::phrase.location'))
            ->options(CountryFacade::buildCountrySearchForm());
    }

    protected function addStartDateField(Section $section): void
    {
        $section->addField(
            Builder::dateTime('start_date')
                ->label(__p('advertise::phrase.start_date'))
                ->placeholder(__p('advertise::phrase.start_date'))
                ->required()
                ->minDate(Carbon::now()->toISOString() ?? '')
                ->yup(
                    Yup::date()
                        ->required(__p('advertise::validation.start_date_is_a_required_field'))
                        ->setError('typeError', __p('advertise::validation.start_date_is_a_required_field'))
                )
        );
    }


    protected function buildDetailOnly(): bool
    {
        return false;
    }

    protected function isEdit(): bool
    {
        return false;
    }

    protected function isAdminCP(): bool
    {
        return false;
    }
}
