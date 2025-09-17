<?php

namespace MetaFox\Advertise\Http\Resources\v1\Advertise\Admin;

use Illuminate\Support\Carbon;
use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Models\Advertise as Model;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Advertise\Traits\HasGenderTrait;
use MetaFox\Advertise\Traits\HasLanguageTrait;
use MetaFox\Core\Support\Facades\Country as CountryFacade;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
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
 * Class CreateAdvertiseForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateAdvertiseForm extends AbstractForm
{
    use HasGenderTrait;
    use HasLanguageTrait;

    /**
     * @var array
     */
    protected array $availablePlacements = [];

    /**
     * @var array
     */
    protected array $availablePlacementDescriptions = [];

    /**
     * @var array
     */
    protected array $availableClickPlacements = [];

    /**
     * @var array
     */
    protected array $availableClickPlacementIds = [];

    /**
     * @var array
     */
    protected array $availableImpressionPlacements = [];

    /**
     * @var array
     */
    protected array $availableImpressionPlacementIds = [];

    /**
     * @param $resource
     */
    public function __construct($resource = null)
    {
        parent::__construct($resource);

        $this->availablePlacements = $this->getAvailablePlacements();

        if (count($this->availablePlacements)) {
            $this->availableClickPlacements = array_filter($this->availablePlacements, function ($placement) {
                return $placement['placement_type'] == Support::PLACEMENT_PPC;
            });

            $this->availableClickPlacementIds = array_column($this->availableClickPlacements, 'value');

            $this->availableImpressionPlacements = array_filter($this->availablePlacements, function ($placement) {
                return $placement['placement_type'] == Support::PLACEMENT_CPM;
            });

            $this->availableImpressionPlacementIds = array_column($this->availableImpressionPlacements, 'value');

            $this->availablePlacementDescriptions = $this->getPlacementDescriptions();
        }
    }

    protected function prepare(): void
    {
        $this->title(__p('advertise::phrase.create_new_advertise'))
            ->action('admincp/advertise/advertise')
            ->asPost()
            ->setBackProps(__p('advertise::phrase.all_ads'))
            ->setValue([
                'creation_type'    => Support::ADVERTISE_IMAGE,
                'is_active'        => 1,
                'start_date'       => Carbon::now()->toISOString(),
                'end_date'         => null,
                'total_click'      => 0,
                'total_impression' => 0,
            ]);
    }

    protected function addCreationTypeField(Section $section): void
    {
        $section->addFields(
            Builder::choice('creation_type')
                ->label(__p('advertise::phrase.advertise_type'))
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
                ->accepts('image/*')
                ->itemType('advertise')
                ->thumbnailSizes($this->getThumbnailSizes())
                ->previewUrl($this->resource?->image)
                ->description(__p('advertise::phrase.recommendation_dimention_for_images'))
                ->yup(
                    Yup::object()
                        ->required()
                        ->addProperty('id', Yup::number()->required(__p('advertise::validation.image_is_a_required_field')))
                ),
            Builder::text('url')
                ->label(__p('advertise::phrase.destination_url'))
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
                ->description(__p('advertise::phrase.image_tooltip_description'))
                ->maxLength(255)
                ->showWhen([
                    'eq',
                    'creation_type',
                    Support::ADVERTISE_IMAGE,
                ]),
        );
    }

    protected function getAdTypes(): array
    {
        return Facade::getAdvertiseTypes();
    }

    protected function addDetailSection(): void
    {
        $section = $this->addSection('detail')
            ->label($this->buildDetailOnly() ? null : __p('advertise::phrase.detail'));

        $section->addFields(
            Builder::title('title')
                ->marginNormal()
                ->label(__p('core::phrase.title'))
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
                ->description(__p('advertise::phrase.no_particular_choices_mean_all_users_can_view'))
                ->multiple()
                ->options($this->getGenderOptions()),
            $this->addLocationField(),
            Builder::text('age_from')
                ->label(__p('advertise::phrase.age_from'))
                ->yup(
                    Yup::number()
                        ->nullable()
                        ->unint(__p('advertise::validation.age_from_must_be_integer'))
                        ->min(1, __p('advertise::validation.age_from_must_be_greater_than_or_equal_to_number', ['number' => 1]))
                        ->setError('typeError', __p('advertise::validation.age_from_must_be_integer'))
                ),
            Builder::text('age_to')
                ->label(__p('advertise::phrase.age_to'))
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
                ->description(__p('advertise::phrase.no_particular_choices_mean_all_users_can_view'))
                ->multiple()
                ->options($this->getLanguageOptions()),
        );

        $this->addStartDateField($section);

        $this->addEndDateField($section);

        $this->addActiveField($section);
    }

    protected function addLocationField(): ?AbstractField
    {
        if (!Settings::get('advertise.enable_advanced_filter', false)) {
            return null;
        }

        return Builder::choice('location')
            ->multiple()
            ->label(__p('core::phrase.location'))
            ->options(CountryFacade::buildCountrySearchForm());
    }

    protected function addStartDateField(Section $section): void
    {
        $min = Carbon::now()
            ->startOfMinute()
            ->toISOString();

        $section->addField(
            Builder::datetime('start_date')
                ->label(__p('advertise::phrase.start_date'))
                ->required()
                ->timeSuggestion()
                ->labelTimePicker(__p('advertise::phrase.start_time'))
                ->labelDatePicker(__p('advertise::phrase.start_date'))
                ->minDateTime($min)
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min($min, __p('advertise::validation.start_date_must_not_be_in_the_past'))
                        ->required(__p('advertise::validation.start_date_is_a_required_field'))
                        ->setError('typeError', __p('advertise::validation.invalid_date'))
                )
        );
    }

    protected function addEndDateField(Section $section): void
    {
        $min = Carbon::now()
            ->startOfMinute()
            ->toISOString();

        if ($this->isEdit()) {
            $min = Carbon::parse($this->resource->start_date)
                ->startOfMinute()
                ->toISOString();
        }

        $section->addField(
            Builder::datetime('end_date')
                ->label(__p('advertise::phrase.end_date'))
                ->labelTimePicker(__p('advertise::phrase.end_time'))
                ->labelDatePicker(__p('advertise::phrase.end_date'))
                ->timeSuggestion()
                ->minDateTime($min)
                ->nullable()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'start_date'])
                        ->setError('typeError', __p('advertise::validation.invalid_date'))
                        ->setError('min', __p('advertise::validation.the_end_date_should_be_greater_than_the_start_date'))
                ),
        );
    }

    protected function addActiveField(Section $section): void
    {
        $section->addField(
            Builder::checkbox('is_active')
                ->label(__p('core::phrase.is_active'))
        );
    }

    protected function addTotalFields(Section $section): void
    {
        $section->addFields(
            Builder::text('total_click')
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
                ->description(__p('advertise::phrase.total_description', ['number' => Support::UNLIMITED_TOTAL]))
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
                                        ->setError('typeError', __p('advertise::validation.total_clicks_must_be_number'))
                                        ->unint(__p('advertise::validation.total_clicks_must_be_number'))
                                        ->min(0, __p('advertise::validation.total_clicks_must_be_greater_than_or_equal_to_number', ['number' => 0]))
                                        ->max(Support::MAX_TOTAL_NUMBER, __p('advertise::validation.total_clicks_must_be_less_than_or_equal_to_number', ['number' => number_format(Support::MAX_TOTAL_NUMBER)]))
                                )
                        )
                ),
            Builder::text('total_impression')
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
                ->description(__p('advertise::phrase.total_description', ['number' => Support::UNLIMITED_TOTAL]))
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
                                        ->setError('typeError', __p('advertise::validation.total_impressions_must_be_number'))
                                        ->required(__p('advertise::validation.total_impressions_is_a_required_field'))
                                        ->unint(__p('advertise::validation.total_impressions_must_be_number'))
                                        ->min(0, __p('advertise::validation.total_impressions_must_be_greater_than_or_equal_to_number', ['number' => 0]))
                                        ->max(Support::MAX_TOTAL_NUMBER, __p('advertise::validation.total_impressions_must_be_less_than_or_equal_to_number', ['number' => number_format(Support::MAX_TOTAL_NUMBER)]))
                                )
                        )
                ),
        );
    }

    protected function getPlacementOptions(): array
    {
        $context = user();

        $currencyId = app('currency')->getUserCurrencyId($context);

        return Facade::getPlacementOptions($context, $this->isAdminCP(), $currencyId, $this->isAdminCP() ? null : true);
    }

    protected function getAvailablePlacements(): array
    {
        $collection = collect($this->getPlacementOptions());

        return $collection->transform(function (array $item) {
            return collect($item)->except(['description']);
        })->values()->toArray();
    }

    protected function getPlacementDescriptions(): array
    {
        $collection = collect($this->getPlacementOptions());

        return $collection->transform(function (array $item) {
            return collect($item)->except(['placement_type', 'label']);
        })->values()->toArray();
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

        $this->addDefaultFooter($this->isEdit());
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
        return true;
    }

    protected function getThumbnailSizes(): array
    {
        return resolve(Advertise::class)
            ->getSizes();
    }
}
