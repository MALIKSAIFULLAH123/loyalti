<?php

namespace MetaFox\Announcement\Http\Requests\v1\Announcement\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Announcement\Models\Style;
use MetaFox\Announcement\Support\Facade\Announcement;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\User\Models\UserGender;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Announcement\Http\Controllers\Api\v1\AnnouncementAdminController::store
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
    public function rules(): array
    {
        return [
            'subject_var'   => ['required', 'array', new TranslatableTextRule()],
            'intro_var'     => ['sometimes', 'array', new TranslatableTextRule(true)],
            'text'          => ['sometimes', 'array', new TranslatableTextRule(true)],
            'is_active'     => ['sometimes', 'integer', new AllowInRule([0, 1])],
            'style'         => ['required', 'integer', new ExistIfGreaterThanZero(sprintf('exists:%s,%s', Style::class, 'id'))],
            'can_be_closed' => ['sometimes', 'integer', new AllowInRule([0, 1])],
            'start_date'    => ['required', 'date'],
            'roles'         => ['sometimes', 'array', 'nullable'],
            'roles.*'       => ['sometimes', 'integer', 'nullable', new AllowInRule(Announcement::getAllowedRole())],
            'countries'     => ['sometimes', 'array', 'nullable'],
            'countries.*'   => ['sometimes', 'string', 'nullable'],
            'genders'       => ['sometimes', 'array'],
            'genders.*'     => [
                'sometimes',
                'numeric',
                new ExistIfGreaterThanZero(sprintf('exists:%s,%s', UserGender::class, 'id')),
            ],
            'age_from' => ['sometimes', 'numeric', 'nullable'],
            'age_to'   => ['sometimes', 'numeric', 'nullable'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data             = parent::validated($key, $default);
        $data['style_id'] = Arr::get($data, 'style') ?? 0;

        $data = Arr::add($data, 'is_active', 1);
        $data = Arr::add($data, 'can_be_closed', 1);

        Arr::set($data, 'subject_var', Language::extractPhraseData('subject_var', $data));
        Arr::set($data, 'intro_var', Language::extractPhraseData('intro_var', $data));
        Arr::set($data, 'text', Language::extractPhraseData('text', $data));

        return $data;
    }
}
