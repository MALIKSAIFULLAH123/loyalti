<?php

namespace MetaFox\Event\Http\Requests\v1\Event;

use Illuminate\Support\Arr;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Event\Http\Controllers\Api\v1\EventController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends StoreRequest
{
    use AttachmentRequestTrait;

    protected Event $event;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id          = $this->route('event');
        $this->event = Event::findOrFail($id);

        $baseRules = [
            'text'           => ['sometimes', 'string', 'nullable'],
            'file'           => ['sometimes', 'array'],
            'file.temp_file' => [
                'required_if:file.status,update', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'file.status'    => ['required_with:file', 'string', new AllowInRule(['update', 'remove'])],
            'privacy'        => ['sometimes', new PrivacyRule([
                'validate_privacy_list' => false,
            ])],
            'image_position' => ['sometimes', 'string', 'nullable'],
        ];

        $fieldRules = [];
        if (!$this->event->isEnded()) {
            $fieldRules = [
                'name'             => ['sometimes', 'required', 'string', new ResourceNameRule('event')],
                'attachments'      => ['sometimes', 'array'],
                'attachments.*.id' => ['sometimes', 'numeric', 'exists:core_attachments,id'],
                'categories'       => ['sometimes', 'array'],
                'categories.*'     => ['numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class), $id)],
                'owner_id'         => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
                'is_online'        => ['required', 'numeric', new AllowInRule([0, 1])],
                'event_url'        => ['required_if:is_online,1', 'nullable', 'url'],
                'start_time'       => ['sometimes', 'required', 'date', 'before:end_time'],
                'end_time'         => [
                    'sometimes', 'required', 'date', $this->getEventDateRule(), 'after:start_time',
                ],
                'location'         => ['required_if:is_online,0', 'nullable', 'array'],
                'host'             => ['sometimes', 'array'],
            ];

            /**@deprecated V5.1.8 remove this */
            if (Settings::get('core.google.google_map_api_key') == null) {
                Arr::forget($fieldRules, 'location');

                Arr::set($fieldRules, 'location_name', ['required_if:is_online,0', 'nullable', 'string']);
            }

            $fieldRules = $this->applyAttachmentRules($fieldRules);
        }

        return array_merge($baseRules, $fieldRules);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data['remove_image'] = false;

        if (isset($data['file']['status'])) {
            $data['remove_image'] = true;
        }

        if ($this->event->isEnded()) {
            unset($data['location_address']);
            unset($data['location_name']);
            unset($data['location_latitude']);
            unset($data['location_longitude']);
            unset($data['country_iso']);
        }

        return $data;
    }
}
