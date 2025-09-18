<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Form\Section;
use MetaFox\Marketplace\Http\Requests\v1\Listing\CreateFormRequest;
use MetaFox\Marketplace\Models\Category;
use MetaFox\Marketplace\Models\Listing as Model;
use MetaFox\Marketplace\Policies\ListingPolicy;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use MetaFox\Marketplace\Support\Facade\Listing;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreListingForm.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreListingForm extends AbstractForm
{
    use PrivacyFieldTrait;

    protected const MAX_ROW_SHORT_DESCRIPTION = 3;

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $categoryRepository = resolve(CategoryRepositoryInterface::class);

        $values = [
            'privacy'             => $this->getPrivacy(),
            'owner_id'            => $this->getOwnerId(),
            'attachments'         => [],
            'location'            => null,
            'allow_payment'       => 0,
            'allow_point_payment' => 0,
            'is_sold'             => 0,
            'auto_sold'           => 1,
            'is_moderator'        => $this->isModerator(),
        ];
        $categoryDefault = $categoryRepository->getCategoryDefault();

        if ($categoryDefault?->is_active == Category::IS_ACTIVE) {
            Arr::set($values, 'categories', [
                $categoryDefault->entityId(),
            ]);
        }

        $this->title(__p('marketplace::phrase.add_new_listing'))
            ->action(url_utility()->makeApiUrl('marketplace'))
            ->asPost()
            ->setBackProps(__p('marketplace::phrase.marketplace'))
            ->setValue($values);
    }

    protected function isModerator(): bool
    {
        return false;
    }

    protected function getPrivacy(): int
    {
        $context = user();

        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'marketplace.item_privacy');

        if (false !== $privacy) {
            return $privacy;
        }

        return MetaFoxPrivacy::EVERYONE;
    }

    protected function getOwnerId(): int
    {
        if (null !== $this->resource) {
            return $this->resource->ownerId();
        }

        if (null !== $this->owner) {
            return $this->owner->entityId();
        }

        return 0;
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $minTitleLength = Listing::getMinimumTitleLength();

        $maxTitleLength = Listing::getMaximumTitleLength();

        $context = user();

        $basic->addFields(
            Builder::text('title')
                ->required()
                ->label(__p('marketplace::phrase.what_are_you_selling'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxTitleLength]))
                ->placeholder(__p('marketplace::phrase.listing_title'))
                ->maxLength($maxTitleLength)
                ->yup(
                    Yup::string()
                        ->required(__p('core::phrase.title_is_a_required_field'))
                        ->minLength(
                            $minTitleLength,
                            __p('core::validation.title_minimum_length_of_characters', [
                                'number' => $minTitleLength,
                            ])
                        )
                ),
        );

        $this->getShortDescriptionField($basic);
        $this->getDescriptionField($basic);

        $this->addPriceFields($basic);

        $basic->addFields(
            $this->buildCategoryField(),
            Builder::attachment()
                ->itemType('marketplace'),
            $this->getLocationField(),
        );

        $this->addAttachedPhotosField($basic);

        $basic->addFields(
            $this->buildAllowPaymentField(),
            $this->buildAllowPointPaymentField(),
            $this->buildAutoSoldField(),
            Builder::hidden('owner_id'),
        );

        if ($this->isEdit()) {
            if ($this->resource->is_approved) {
                $basic->addFields(
                    Builder::switch('is_sold')
                        ->label(__p('marketplace::phrase.closed_item_sold'))
                        ->description(__p('marketplace::phrase.enable_close_option_listing_closed'))
                );
            }
        }

        $basic->addFields(
            Listing::enableTopic($context, $this->owner) ? Builder::tags()
                ->label(__p('marketplace::phrase.product_tags'))
                ->placeholder(__p('core::phrase.keywords')) : null,
            Builder::text('external_link')
                ->label(__p('core::phrase.external_link'))
                ->placeholder(__p('core::phrase.external_link'))
                ->description(__p('marketplace::phrase.external_link_desc'))
                ->yup(
                    Yup::string()
                        ->url(__p('marketplace::validation.external_link_must_be_a_valid_url'))
                ),
            $this->buildPrivacyField()
                ->description(__p('marketplace::phrase.control_who_can_see_this_listing')),
        );

        $this->addDefaultFooter($this->isEdit());
    }

    /**
     * @throws AuthenticationException
     */
    protected function buildAllowPaymentField(): ?AbstractField
    {
        $context = user();

        $paymentSettingUrl = null;

        if (method_exists($context, 'toPaymentSettingUrl')) {
            $paymentSettingUrl = call_user_func([$context, 'toPaymentSettingUrl']);
        }

        if (!$context->hasPermissionTo('marketplace.sell_items')) {
            return null;
        }

        return Builder::switch('allow_payment')
            ->label(__p('marketplace::phrase.enable_instant_payment'))
            ->description(__p('marketplace::phrase.enable_instant_payment_description', [
                'hasLink' => $paymentSettingUrl ? 1 : 0,
                'link'    => $paymentSettingUrl ?: '',
            ]));
    }

    /**
     * @throws AuthenticationException
     */
    protected function buildAllowPointPaymentField(): ?AbstractField
    {
        if (!Listing::isActivityPointAppActive()) {
            return Builder::hidden('allow_point_payment');
        }

        $context = user();

        if (
            !$context->hasPermissionTo('marketplace.enable_activity_point_payment')
            || !$context->hasPermissionTo('marketplace.sell_items')
        ) {
            return null;
        }

        return Builder::switch('allow_point_payment')
            ->label(__p('marketplace::phrase.enable_point_payment'))
            ->description(__p('marketplace::phrase.enable_point_payment_description'));
    }

    protected function buildAutoSoldField(): ?AbstractField
    {
        $context = user();

        if (!$context->hasPermissionTo('marketplace.sell_items')) {
            return null;
        }

        return Builder::switch('auto_sold')
            ->label(__p('marketplace::phrase.auto_sold'))
            ->description(__p('marketplace::phrase.auto_sold_description'));
    }

    protected function addAttachedPhotosField(Section $basic): void
    {
        $context = user();

        $maxUpload = (int) $context->getPermissionValue('marketplace.maximum_number_of_attached_photos_per_upload');

        $fileSize = file_type()->getFilesizeInMegabytes('photo');

        $field = Builder::uploadMultiMedia('attached_photos')
            ->label(__p('core::phrase.add_photos'))
            ->placeholder(__p('core::phrase.drag_and_drop_photos_upload'))
            ->description(__p('marketplace::phrase.upload_attached_photos_description', [
                'max_file_size'        => $fileSize,
                'has_limit_per_upload' => $maxUpload > 0 ? 1 : 0,
                'max_per_upload'       => $maxUpload,
            ]))
            ->accepts('image/*')
            ->required()
            ->allowDrop()
            ->maxFiles($maxUpload)
            ->itemType('marketplace')
            ->uploadUrl('file');

        $yupObjectPhoto = Yup::object()
            ->addProperty('id', Yup::number())
            ->addProperty('type', Yup::string())
            ->addProperty('status', Yup::string());

        $yup = Yup::array()->min(1, __p('marketplace::phrase.attached_photos_is_a_required_field'))
            ->minWhen([
                'value' => 1,
                'when'  => [
                    'includes', 'item.status',
                    [MetaFoxConstant::FILE_NEW_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS],
                ],
            ], __p('validation.min.array', [
                'min'       => 1,
                'attribute' => __p('marketplace::phrase.attached_photos'),
            ]))
            ->of($yupObjectPhoto);

        /*
         * In case value is 0, it means unlimit
         */
        if ($maxUpload > 0) {
            $yup->maxWhen([
                'value' => $maxUpload,
                'when'  => [
                    'includes', 'item.status',
                    [MetaFoxConstant::FILE_NEW_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS],
                ],
            ], __p('marketplace::phrase.maximum_per_upload_limit_reached', ['limit' => $maxUpload]))
                ->of($yupObjectPhoto);
        }

        $field->yup($yup);

        $basic->addField($field);
    }

    protected function addPriceFields(Section $basic): void
    {
        $currencies   = app('currency')->getActiveOptions();
        $userCurrency = app('currency')->getUserCurrencyId(user());
        $description  = __p('marketplace::phrase.amount_you_want_to_sell');
        $options      = [];
        $yup          = Yup::object()->required(__p(
            'marketplace::phrase.price_must_be_at_least_number_currency',
            ['number' => 1]
        ));
        $maxValue = (int) str_repeat(9, 12);

        foreach ($currencies as $currency) {
            $value = Arr::get($currency, 'value');
            Arr::set($currency, 'value', MetaFoxConstant::EMPTY_STRING);
            Arr::set($currency, 'key', $value);
            Arr::set($currency, 'description', $description);
            Arr::set($currency, 'required', $userCurrency == $value);
            $options[] = $currency;

            $subYup = Yup::number()
                ->nullable()
                ->positive(__p('marketplace::validation.currency_must_be_a_positive_number', [
                    'currency_code' => $currency['label'],
                ]))
                ->max($maxValue, __p('core::validation.currency_must_be_less_than_or_equal_to_number', [
                    'currency_code' => $currency['label'],
                    'number'        => number_format($maxValue),
                ]))
                ->setError('positive', __p('marketplace::validation.currency_must_be_a_positive_number', [
                    'currency_code' => $currency['label'],
                ]))
                ->setError('typeError', __p('core::validation.numeric', [
                    'attribute' => $currency['label'],
                ]));

            if ($userCurrency == $value) {
                $subYup->required(__p('validation.required', [
                    'attribute' => $currency['label'],
                ]));
            }

            $yup->addProperty($value, $subYup);
        }

        $options = array_values(Arr::sortDesc($options, fn (array $ar) => $ar['required']));

        $basic->addField(
            Builder::price('price')
                ->label(__p('core::phrase.price'))
                ->maxLength(12)
                ->sizeSmall()
                ->options($options)
                ->yup($yup)
        );
    }

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(CreateFormRequest $request, ?int $id = null)
    {
        $data = $request->validated();

        $ownerId = Arr::get($data, 'owner_id');

        $context = user();

        $this->setOwner($context);

        if ($ownerId > 0) {
            $owner = UserEntity::getById($ownerId)->detail;
            $this->setOwner($owner);
        }

        app('quota')->checkQuotaControlWhenCreateItem($context, Model::ENTITY_TYPE, 1, ['messageFormat' => 'text']);
        policy_authorize(ListingPolicy::class, 'create', $context, $this->owner);
    }

    protected function isEdit(): bool
    {
        return false;
    }

    protected function buildCategoryField(): AbstractField
    {
        $field = Builder::category('categories')
            ->label(__p('core::phrase.category'))
            ->multiple(false)
            ->required()
            ->setRepository(CategoryRepositoryInterface::class)
            ->yup(
                Yup::number()
                    ->required(__p('marketplace::phrase.category_is_a_required_field'))
            );

        if ($this->isEdit()) {
            $field->setSelectedCategories($this->resource->categories);
        }

        return $field;
    }

    protected function getLocationField(): AbstractField
    {
        if (Settings::get('core.google.google_map_api_key') == null) {
            return Builder::text('location_name')
                ->label(__p('core::phrase.location'))
                ->required()
                ->yup(Yup::string()
                    ->required(__p('marketplace::phrase.location_is_a_required_field')));
        }

        return Builder::location('location')
            ->required()
            ->placeholder(__p('marketplace::phrase.enter_location'))
            ->setError('required', __p('marketplace::phrase.location_is_a_required_field'))
            ->setError('typeError', __p('marketplace::phrase.location_is_a_required_field'))
            ->yup(
                Yup::object()
                    ->nullable()
                    ->required(__p('marketplace::phrase.location_is_a_required_field'))
                    ->addProperty(
                        'lat',
                        Yup::number()
                            ->nullable()
                    )
                    ->addProperty(
                        'lng',
                        Yup::number()
                            ->nullable()
                    )
                    ->addProperty(
                        'address',
                        Yup::string()
                            ->nullable()
                    )
                    ->addProperty(
                        'short_name',
                        Yup::string()
                            ->nullable()
                    )
            );
    }

    protected function getShortDescriptionField(Section $basic): void
    {
        if (!Settings::get('marketplace.enable_short_description_field')) {
            return;
        }

        $basic->addField(Builder::textArea('short_description')
            ->maxLength(MetaFoxConstant::DEFAULT_MAX_SHORT_DESCRIPTION_LENGTH)
            ->label(__p('marketplace::phrase.short_description'))
            ->rows(self::MAX_ROW_SHORT_DESCRIPTION)
            ->placeholder(__p('marketplace::phrase.type_something_dot')));
    }

    protected function getDescriptionField(Section $basic): void
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            $basic->addField(
                Builder::richTextEditor('text')
                    ->required()
                    ->label(__p('core::phrase.description'))
                    ->placeholder(__p('marketplace::phrase.type_something_dot'))
                    ->yup(
                        Yup::string()
                            ->required()
                    ),
            );
        } else {
            $basic->addField(
                Builder::textArea('text')
                    ->required()
                    ->label(__p('core::phrase.description'))
                    ->placeholder(__p('marketplace::phrase.type_something_dot'))
                    ->yup(
                        Yup::string()
                            ->required()
                    ),
            );
        }
    }
}
