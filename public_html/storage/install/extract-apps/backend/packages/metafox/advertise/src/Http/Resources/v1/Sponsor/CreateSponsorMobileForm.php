<?php

namespace MetaFox\Advertise\Http\Resources\v1\Sponsor;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Advertise\Policies\SponsorPolicy;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Advertise\Support\Form\Mobile\SponsorCalculatorCost;
use MetaFox\Advertise\Traits\HasGenderTrait;
use MetaFox\Advertise\Traits\HasLanguageTrait;
use MetaFox\Core\Support\Facades\Country as CountryFacade;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Yup\Yup;
use MetaFox\Advertise\Models\Sponsor as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateSponsorMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateSponsorMobileForm extends AbstractForm
{
    use HasGenderTrait;
    use HasLanguageTrait;
    /**
     * @var Content|null
     */
    protected ?Content $item = null;

    /**
     * @var bool
     */
    protected bool $isFree = false;

    protected function prepare(): void
    {
        $sponsorData = $this->item->toSponsorData();

        $title = Arr::get($sponsorData, 'title');

        if (is_string($title) && Str::length($title) > 255) {
            $title = Str::substr($title, 0, 255);
        }

        $this->title(__p('advertise::phrase.sponsor_item'))
            ->action('advertise/sponsor')
            ->asPost()
            ->setValue([
                'title'            => $title,
                'item_type'        => $this->item->entityType(),
                'item_id'          => $this->item->entityId(),
                'total_impression' => 1000,
                'start_date'       => Carbon::now()->toISOString(),
                'end_date'         => null,
                'age_from'         => '',
            ]);
    }

    protected function initialize(): void
    {
        $section = $this->addSection('detail')
            ->addFields(
                Builder::text('title')
                    ->marginNormal()
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

        $this->addEndDateField($section);
    }

    protected function isEdit(): bool
    {
        return false;
    }

    protected function addStartDateField(Section $section): void
    {
        if ($this->isEdit() && !Support::isFreeSponsorInvoice($this->resource)) {
            return;
        }

        $section->addField(
            Builder::dateTime('start_date')
                ->disabled($this->isEdit())
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

    protected function addEndDateField(Section $section): void
    {
        if (!$this->isFree) {
            return;
        }

        $disabled = $this->resource instanceof Sponsor && $this->resource->id && $this->resource->is_ended;

        $description = null;

        if ($disabled) {
            $description = __p('advertise::phrase.you_cant_edit_the_end_date_because_the_sponsorship_has_ended');
        }

        $section->addField(
            Builder::dateTime('end_date')
                ->label(__p('advertise::phrase.end_date'))
                ->placeholder(__p('advertise::phrase.end_date'))
                ->disabled($disabled)
                ->minDate(Carbon::now()->toISOString() ?? '')
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

    protected function addTotalFields(Section $section): void
    {
        $section->addFields(
            (new SponsorCalculatorCost())
                ->name('total_impression')
                ->required()
                ->label(__p('advertise::phrase.total_impressions'))
                ->placeholder(__p('advertise::phrase.total_impressions'))
                ->initialPrice($this->getInitialPrice())
                ->yup(
                    Yup::number()
                        ->required(__p('advertise::validation.total_impressions_is_a_required_field'))
                        ->min(100, __p('advertise::validation.total_impressions_must_be_greater_than_or_equal_to_number', ['number' => 100]))
                        ->unint(__p('advertise::validation.total_impressions_must_be_number'))
                        ->setError('typeError', __p('advertise::validation.total_impressions_must_be_number')),
                )
        );
    }

    protected function getInitialPrice(): ?float
    {
        return resolve(SponsorSettingServiceInterface::class)->getPriceForPayment(user(), $this->item);
    }

    /**
     * @param  int|null                $id
     * @param  string|null             $itemType
     * @param  int|null                $itemId
     * @return void
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(?int $id = null, ?string $itemType = null, ?int $itemId = null): void
    {
        $this->item = resolve(SponsorRepositoryInterface::class)->getMorphedItem($itemType, $itemId);

        $context = user();

        if (null === $this->item) {
            abort(403, __p('advertise::validation.this_item_is_not_available_for_sponsor'));
        }

        if ($this->item instanceof HasPrivacy && $this->item->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }

        policy_authorize(SponsorPolicy::class, 'purchaseSponsor', $context, $this->item);

        $this->isFree = $this->getInitialPrice() == 0;
    }
}
