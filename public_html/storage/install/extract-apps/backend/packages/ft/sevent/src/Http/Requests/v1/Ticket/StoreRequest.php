<?php

namespace Foxexpert\Sevent\Http\Requests\v1\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Platform\Rules\AllowInRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Rules\ResourceTextRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;
use MetaFox\Platform\Traits\Http\Request\TagRequestTrait;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Facades\Settings;
/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    use PrivacyRequestTrait;
    use AttachmentRequestTrait;
    use TagRequestTrait;

    public const ACTION_CAPTCHA_NAME = 'create-ticket';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxUpload = 10;
        $rules = [
            'title'          => ['required', 'string', new ResourceNameRule('ticket')],
            'owner_id'       => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'file'           => ['nullable', 'array'],
            'file.temp_file' => ['numeric', 'exists:storage_files,id'],
            'description'           => ['required', 'string', new ResourceTextRule(true)],
            'amount'          => ['numeric', 'string'],
            'qty'          => ['nullable'],
            'expiry_date'       => ['nullable', 'date'],
            'sevent_id' => ['required','integer'],
            'is_unlimited'     => ['sometimes', 'numeric', new AllowInRule([true, false])],
        ];

        $rules            = $this->applyAttachmentRules($rules);
        $rules            = $this->applyTagRules($rules);
        $rules['captcha'] = Captcha::ruleOf('sevent.create_sevent');
        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        $data['temp_file'] = 0;
        if (isset($data['file']['temp_file'])) {
            $data['temp_file'] = $data['file']['temp_file'];
        }

        if (isset($data['expiry_date'])) {
            $data['expiry_date'] = Carbon::parse($data['expiry_date'])->toDateTimeString();
        }

        if (empty($data['owner_id'])) {
            $data['owner_id'] = 0;
        }

        return $data;
    }
}