<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Listing;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Marketplace\Models\Image;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Marketplace\Rules\MaximumAttachedPhotosPerUpload;
use MetaFox\Marketplace\Support\Facade\Listing;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceTextRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

class StoreRequest extends FormRequest
{
    use PrivacyRequestTrait;
    use AttachmentRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        $minLength = Listing::getMinimumTitleLength();

        $maxLength = Listing::getMaximumTitleLength();

        $context = user();

        $maxUpload = (int) $context->getPermissionValue('marketplace.maximum_number_of_attached_photos_per_upload');

        $rules = [
            'title'                       => ['required', 'string', 'between:' . $minLength . ',' . $maxLength],
            'categories'                  => ['required', 'array'],
            'categories.*'                => ['numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class))],
            'short_description'           => [
                'sometimes', 'nullable', 'string', 'between:1,' . MetaFoxConstant::DEFAULT_MAX_SHORT_DESCRIPTION_LENGTH,
            ],
            'text'                        => ['sometimes', 'string', new ResourceTextRule()],
            'owner_id'                    => [
                'sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id'),
            ],
            'external_link'               => ['sometimes', 'url', 'nullable'],
            'privacy'                     => ['required', new PrivacyRule()],
            'location'                    => ['required', 'array'],
            'location.lat'                => ['sometimes', 'nullable', 'numeric'],
            'location.lng'                => ['sometimes', 'nullable', 'numeric'],
            'location.address'            => ['required', 'string'],
            'location.full_address'       => ['sometimes', 'nullable', 'string'],
            'location.short_name'         => ['sometimes', 'nullable', 'string'],
            'attached_photos'             => ['required', 'array', new MaximumAttachedPhotosPerUpload($maxUpload)],
            'attached_photos.*.id'        => [
                'required_if:attached_photos.*.status,' . implode(',', [MetaFoxConstant::FILE_REMOVE_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS]),
                'numeric',
                new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'attached_photos.*.status'    => [
                'required_with:attached_photos', new AllowInRule([
                    MetaFoxConstant::FILE_REMOVE_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS,
                    MetaFoxConstant::FILE_NEW_STATUS,
                ]),
            ],
            'attached_photos.*.temp_file' => [
                'required_if:attached_photos.*.status,' . MetaFoxConstant::FILE_NEW_STATUS, 'numeric',
                new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'attached_photos.*.file_type' => [
                'required_if:attached_photos.*.status,' . MetaFoxConstant::FILE_NEW_STATUS, 'string', new AllowInRule(
                    ['photo'],
                    __p('marketplace::phrase.the_attached_photos_are_invalid')
                ),
            ],
            'attached_photos.*.ordering'  => ['sometimes', 'numeric'],
            'is_sold'                     => ['sometimes', 'numeric', new AllowInRule([true, false])],
            'auto_sold'                   => ['sometimes', 'numeric', new AllowInRule([true, false])],
            'tags'                        => ['sometimes', 'array'],
            'price'                       => ['array'],
        ];

        /**@deprecated V5.1.8 remove this */
        if (Settings::get('core.google.google_map_api_key') == null) {
            Arr::forget($rules, ['location', 'location.lat', 'location.lng', 'location.address', 'location.short_name', 'location.full_address']);
            Arr::set($rules, 'location_name', ['required', 'string']);
        }

        $rules = $this->applyAllowPaymentRules($rules);

        $rules = $this->applyAllowPointPaymentRules($rules);

        $rules = $this->applyAttachmentRules($rules);

        $rules = $this->applyPriceRules($rules);

        return $rules;
    }

    /**
     * @throws AuthenticationException
     */
    protected function applyAllowPaymentRules($rules): array
    {
        $rules['allow_payment'] = [
            'sometimes', new AllowInRule($this->canAllowPayment() ? [0, 1] : [0]),
        ];

        return $rules;
    }

    /**
     * @throws AuthenticationException
     */
    protected function applyAllowPointPaymentRules($rules): array
    {
        $rules['allow_point_payment'] = [
            'sometimes', new AllowInRule($this->canAllowPointPayment() && $this->canAllowPayment() ? [0, 1] : [0]),
        ];

        return $rules;
    }

    /**
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'price.*.value.required' => __p('marketplace::phrase.price_is_a_required_field'),
            'external_link.url'      => __p('core::validation.external_link.url'),
        ];
    }

    /**
     * @param array $rules
     * @return array
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    protected function applyPriceRules(array $rules): array
    {
        if (Metafox::isMobile() && -1 === version_compare(MetaFox::getApiVersion(), 'v1.8')) {
            $rules['price.*.value'] = ['required', 'numeric', 'gte:0', 'lte:' . str_repeat(9, 12)];
            $rules['price.*.label'] = ['required'];
            return $rules;
        }

        $context = user();

        if ($context->isGuest()) {
            throw new AuthorizationException();
        }

        $currencies   = app('currency')->getActiveOptions();

        if (!count($currencies)) {
            throw new AuthorizationException();
        }

        $userCurrency = app('currency')->getUserCurrencyId($context);

        foreach ($currencies as $currency) {
            $value = Arr::get($currency, 'value');
            if ($value !== $userCurrency) {
                $rules[sprintf('price.%s', $value)] = [
                    'sometimes', 'nullable', 'numeric', 'gte:0', 'lte:' . str_repeat(9, 12),
                ];

                continue;
            }

            $rules[sprintf('price.%s', $value)] = [
                'required', 'numeric', 'gte:0', 'lte:' . str_repeat(9, 12),
            ];
        }

        return $rules;
    }

    /**
     * @throws AuthenticationException
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data = $this->handlePrivacy($data);

        if (null === Arr::get($data, 'owner_id')) {
            Arr::set($data, 'owner_id', 0);
        }

        $location = Arr::get($data, 'location');

        if (is_array($location)) {
            Arr::set($data, 'location_address', Arr::get($location, 'full_address'));

            Arr::set($data, 'location_name', Arr::get($location, 'address'));

            Arr::set($data, 'location_latitude', Arr::get($location, 'lat'));

            Arr::set($data, 'location_longitude', Arr::get($location, 'lng'));

            Arr::set($data, 'country_iso', Arr::get($location, 'short_name'));

            unset($data['location']);
        }

        if (null === Arr::get($data, 'is_sold')) {
            Arr::set($data, 'is_sold', 0);
        }

        if (null === Arr::get($data, 'privacy')) {
            Arr::set($data, 'privacy', MetaFoxPrivacy::EVERYONE);
        }

        $data = $this->validatedAllowPayment($data);

        if (MetaFox::isMobile() && -1 === version_compare(MetaFox::getApiVersion(), 'v1.8')) {
            Arr::set($data, 'price', $this->validatedPrice($data));
        }

        if (Arr::has($data, 'price')) {
            $prices = Arr::get($data, 'price', []);

            foreach ($prices as $currency => $price) {
                if (!is_numeric($price)) {
                    continue;
                }

                Arr::set($prices, $currency, round($price, 2));
            }

            Arr::set($data, 'price', $prices);
        }

        return $this->compatibleOrderingWithOldMobileVersion($data);
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function compatibleOrderingWithOldMobileVersion(array $attributes): array
    {
        if (!MetaFox::isMobile() || version_compare(MetaFox::getApiVersion(), 'v1.13', '>=')) {
            return $attributes;
        }

        $attachedPhotos = Arr::get($attributes, 'attached_photos');

        if (!is_array($attachedPhotos)) {
            return $attributes;
        }

        $listing = $this->getListing();

        $order = 1;

        if ($listing instanceof \MetaFox\Marketplace\Models\Listing) {
            $order = (int) Image::query()
                ->where('listing_id', '=', $listing->entityId())
                ->max('ordering');

            $totalImages = Image::query()
                ->where('listing_id', '=', $listing->entityId())
                ->count();

            if ($order < $totalImages) {
                $order = $totalImages;
            }

            $order += 1;
        }

        foreach ($attachedPhotos as $key => $photo) {
            $tempFile = Arr::get($photo, 'temp_file');

            if (!is_numeric($tempFile)) {
                continue;
            }

            Arr::set($photo, 'ordering', $order);

            $order++;

            $attachedPhotos[$key] = $photo;
        }

        Arr::set($attributes, 'attached_photos', $attachedPhotos);

        return $attributes;
    }

    /**
     * @throws AuthenticationException
     */
    protected function validatedAllowPayment($data): array
    {
        if (!$this->canAllowPointPayment() || !$this->canAllowPayment()) {
            Arr::set($data, 'allow_point_payment', 0);
        }

        if (!$this->canAllowPayment()) {
            Arr::set($data, 'allow_payment', 0);
        }

        return $data;
    }

    protected function validatedPrice(array $attributes): array
    {
        $values = [];

        foreach ($attributes['price'] as $item) {
            $values[$item['label']] = round(Arr::get($item, 'value'), 2);
        }

        return $values;
    }

    /**
     * @throws AuthenticationException
     */
    protected function canAllowPointPayment(): bool
    {
        $context = user();

        return $context->hasPermissionTo('marketplace.enable_activity_point_payment');
    }

    /**
     * @throws AuthenticationException
     */
    protected function canAllowPayment(): bool
    {
        $context = user();

        return $context->hasPermissionTo('marketplace.sell_items');
    }

    protected function getListing(): ?\MetaFox\Marketplace\Models\Listing
    {
        $id = request()->route('marketplace');

        if (!is_numeric($id)) {
            return null;
        }

        return resolve(ListingRepositoryInterface::class)->find($id);
    }
}
