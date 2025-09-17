<?php

namespace MetaFox\Advertise\Http\Requests\v1\Sponsor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Rules\EndDateRule;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Contracts\Content;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Advertise\Http\Controllers\Api\v1\SponsorController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $ageFrom = request()->get('age_from');

        $isAdminCP = $this->isAdminCP();

        $isEdit = $this->isEdit();

        $rules = [
            'title'       => ['required', 'string', 'max: ' . MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH],
            'genders'     => ['nullable', 'array'],
            'genders.*'   => ['required_with:genders', 'exists:user_gender,id'],
            'age_from'    => ['nullable', 'integer', 'min:1'],
            'age_to'      => is_numeric($ageFrom) ? ['nullable', 'integer', 'min:' . $ageFrom] : ['nullable'],
            'languages'   => ['nullable', 'array'],
            'languages.*' => ['required_with:languages', 'string', 'exists:core_languages,language_code,is_active,1'],
            'location'    => ['sometimes', 'nullable', 'array', 'min:0'],
        ];

        if (!$isEdit || $isAdminCP) {
            $rules = array_merge($rules, [
                'start_date' => ['required', 'string', 'date'],
            ]);
        }

        $rules = $this->addEndDateRule($rules);

        if (!$isEdit) {
            $rules = array_merge($rules, [
                'item_type'        => ['required', 'string'],
                'item_id'          => ['required', 'integer'],
                'total_impression' => ['required', 'integer', 'min:100'],
            ]);
        }

        return $rules;
    }

    protected function isAdminCP(): bool
    {
        return false;
    }

    protected function isEdit(): bool
    {
        return false;
    }

    protected function addEndDateRule(array $rules): array
    {
        if ($this->isAdminCP()) {
            return array_merge($rules, [
                'end_date' => [new EndDateRule()],
            ]);
        }

        $sponsor = $this->getSponsor();

        if (!$this->isFreePrice($sponsor)) {
            return $rules;
        }

        if (null === $sponsor) {
            return array_merge($rules, [
                'end_date' => [new EndDateRule()],
            ]);
        }

        if ($sponsor->is_ended) {
            return $rules;
        }

        return array_merge($rules, [
            'end_date' => [new EndDateRule()],
        ]);
    }

    protected function getMorphedModel(): ?Content
    {
        $itemType = request()->get('item_type');

        $itemId = request()->get('item_id');

        if (!$itemType || !$itemId) {
            return null;
        }

        return resolve(SponsorRepositoryInterface::class)->getMorphedItem($itemType, $itemId);
    }

    protected function getSponsor(): ?Sponsor
    {
        if (!$this->isEdit()) {
            return null;
        }

        return resolve(SponsorRepositoryInterface::class)->find(request()->route('sponsor'));
    }

    protected function isFreePrice(?Sponsor $sponsor): bool
    {
        if (null !== $sponsor) {
            return Support::isFreeSponsorInvoice($sponsor);
        }

        $item = $this->getMorphedModel();

        if (null === $item) {
            return false;
        }

        if (resolve(SponsorSettingServiceInterface::class)->getPriceForPayment(user(), $item) != 0) {
            return false;
        }

        return true;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->prepareLanguages($data);

        $data = $this->prepareGenders($data);

        $data = $this->prepareLocation($data);

        if (!is_numeric(Arr::get($data, 'age_from'))) {
            Arr::set($data, 'age_to', null);
        }

        $data = $this->validateEndDate($data);

        return $data;
    }

    protected function validateEndDate(array $data): array
    {
        if (!Arr::has($data, 'end_date')) {
            return $data;
        }

        if ($this->isAdminCP()) {
            return $data;
        }

        $sponsor = $this->getSponsor();

        if (!$this->isFreePrice($sponsor)) {
            Arr::forget($data, 'end_date');

            return $data;
        }

        if (null === $sponsor) {
            return $data;
        }

        if ($sponsor->is_ended) {
            Arr::forget($data, 'end_date');
        }

        return $data;
    }

    protected function prepareLocation(array $data): array
    {
        $locations = Arr::get($data, 'location');

        if (null === $locations) {
            return $data;
        }

        if (!is_array($locations)) {
            Arr::set($data, 'location', null);

            return $data;
        }

        return $data;
    }

    protected function prepareGenders(array $data): array
    {
        $genders = Arr::get($data, 'genders');

        if (null === $genders) {
            return $data;
        }

        if (!is_array($genders) || !count($genders)) {
            Arr::set($data, 'genders', null);
        }

        return $data;
    }

    protected function prepareLanguages(array $data): array
    {
        $languages = Arr::get($data, 'languages');

        if (null === $languages) {
            return $data;
        }

        if (!is_array($languages) || !count($languages)) {
            Arr::set($data, 'languages', null);
        }

        return $data;
    }
}
