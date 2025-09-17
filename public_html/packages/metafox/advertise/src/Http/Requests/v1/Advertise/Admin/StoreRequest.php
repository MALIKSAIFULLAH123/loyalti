<?php

namespace MetaFox\Advertise\Http\Requests\v1\Advertise\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Rules\EndDateRule;
use MetaFox\Advertise\Rules\TotalClickRule;
use MetaFox\Advertise\Rules\TotalImpressionRule;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\ValidImageRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Advertise\Http\Controllers\Api\v1\AdvertiseAdminController::store
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
        $isEdit = $this->isEdit();

        $isAdminCP = $this->isAdminCP();

        $rules = [
            'placement_id'     => ['required', 'numeric', 'exists:advertise_placements,id'],
            'image'            => [$isEdit ? 'sometimes' : 'required', 'array', resolve(ValidImageRule::class, ['isRequired' => true])],
            'image.status'     => ['required_with:image.temp_file', 'string', new AllowInRule([MetaFoxConstant::FILE_UPDATE_STATUS])],
            'image.temp_file'  => ['required_if:image.id,0', 'numeric', 'exists:storage_files,id'],
            'url'              => ['required', 'url'],
            'image_tooltip'    => ['nullable', 'string', 'max:' . MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH],
            'html_title'       => ['required_if:creation_type,' . Support::ADVERTISE_HTML, 'max:' . Support::MAX_HTML_TITLE_LENGTH],
            'html_description' => ['nullable', 'string', 'max:' . Support::MAX_HTML_DESCRIPTION_LENGTH],
            'title'            => ['required', 'string', 'max: ' . MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH],
            'total_click'      => ['sometimes', 'numeric', new TotalClickRule($isAdminCP)],
            'total_impression' => ['sometimes', 'numeric', new TotalImpressionRule($isAdminCP)],
            'genders'          => ['nullable', 'array'],
            'genders.*'        => ['required_with:genders', 'numeric', new ExistIfGreaterThanZero('exists:user_gender,id')],
            'age_from'         => ['nullable', 'integer', 'min:1'],
            'age_to'           => ['nullable', 'integer', 'gte:age_from'],
            'languages'        => ['nullable', 'array'],
            'languages.*'      => ['required_with:languages', 'string', 'exists:core_languages,language_code,is_active,1'],
            'is_active'        => [$isAdminCP ? 'required' : 'nullable', new AllowInRule([0, 1])],
            'start_date'       => ['required', 'string', 'date'],
            'end_date'         => [$isAdminCP ? new EndDateRule() : 'nullable'],
            'creation_type'    => ['required', new AllowInRule($this->getAdvertiseTypeOptions())],
            'location'         => ['sometimes', 'nullable', 'array', 'min:0'],
        ];

        return $rules;
    }

    protected function isAdminCP(): bool
    {
        return true;
    }

    protected function isEdit(): bool
    {
        return false;
    }

    protected function getAdvertiseTypeOptions(): array
    {
        return array_column(Facade::getAdvertiseTypes(), 'value');
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->prepareLanguages($data);

        $data = $this->prepareGenders($data);

        $data = $this->prepareLocation($data);

        if ($this->isAdminCP() || !$this->isEdit()) {
            $data = $this->prepareImageAdvertise($data);
            $data = $this->prepareHTMLAdvertise($data);
        }

        if (!is_numeric(Arr::get($data, 'age_from'))) {
            Arr::set($data, 'age_to', null);
        }

        if ($this->isEdit()) {
            unset($data['creation_type']);
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

    protected function prepareImageAdvertise(array $data): array
    {
        $type = Arr::get($data, 'creation_type');

        if ($type != Support::ADVERTISE_IMAGE) {
            Arr::set($data, 'image_values', null);

            return $data;
        }

        Arr::set($data, 'image_values', [
            'image_tooltip' => Arr::get($data, 'image_tooltip'),
        ]);

        return $data;
    }

    protected function prepareHTMLAdvertise(array $data): array
    {
        $type = Arr::get($data, 'creation_type');

        if ($type != Support::ADVERTISE_HTML) {
            Arr::set($data, 'html_values', null);

            return $data;
        }

        Arr::set($data, 'html_values', [
            'html_title'       => Arr::get($data, 'html_title'),
            'html_description' => Arr::get($data, 'html_description'),
        ]);

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
