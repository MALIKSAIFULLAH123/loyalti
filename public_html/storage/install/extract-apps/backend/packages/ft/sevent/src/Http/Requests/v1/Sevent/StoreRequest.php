<?php

namespace Foxexpert\Sevent\Http\Requests\v1\Sevent;

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
use MetaFox\Core\Rules\DateEqualOrAfterRule;
use MetaFox\Platform\Facades\Settings;
/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    use PrivacyRequestTrait;
    use AttachmentRequestTrait;
    use TagRequestTrait;

    public const ACTION_CAPTCHA_NAME = 'create-sevent';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxUpload = 10;
        $rules = [
            'title'          => ['required', 'string', new ResourceNameRule('sevent')],
            'is_online'        => ['required', 'numeric', new AllowInRule([0, 1])],
            'online_link'        => ['required_if:is_online,1', 'nullable', 'url'],
            'start_date'       => ['required', 'date', 'before:end_date'],
            'end_date'         => ['required', 'date', $this->getEventDateRule(), 'after:start_date'],
            'video' => ['nullable', 'string'],
            'course_id'  => ['nullable', 'numeric'],
            'short_description' => ['nullable', 'string'],
            'terms'             => ['sometimes', 'string', 'nullable'],
            'categories'     => ['sometimes', 'array'],
            'categories.*'   => ['numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class))],
            'owner_id'       => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'file'           => ['sometimes', 'array'],
            'file.temp_file' => ['required_with:file', 'numeric', 'exists:storage_files,id'],

            'host_image'           => ['sometimes', 'array'],
            'host_image.temp_file' => ['required_with:host_image', 'numeric', 'exists:storage_files,id'],
            'is_host'             => ['sometimes', 'integer', 'nullable'],
            'host_title'             => ['sometimes', 'string', 'nullable'],
            'host_contact'             => ['sometimes', 'string', 'nullable'],
            'host_website'             => ['sometimes', 'string', 'nullable'],
            'host_facebook'             => ['sometimes', 'string', 'nullable'],
            'host_description'             => ['sometimes', 'string', 'nullable'],
            

            'text'           => ['required', 'string', new ResourceTextRule(true)],
            'draft'          => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'tags.*'         => ['string'],
            'privacy'        => ['required', new PrivacyRule()],
            'captcha'        => Captcha::ruleOf('sevent.create_sevent'),
            'location'         => ['required_if:is_online,0', 'nullable', 'array'],
            'location.lat'                => ['sometimes', 'nullable'],
            'location.lng'                => ['sometimes', 'nullable'],
            'location.address'            => ['sometimes', 'nullable'],
            'location.short_name'         => ['sometimes', 'nullable'],

            'attached_photos'             => ['sometimes', 'array'],
            'attached_photos.*.id'        => [
                'required_if:attached_photos.*.status,update,remove', 'numeric',
                new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'attached_photos.*.status'    => [
                'required_with:attached_photos', new AllowInRule([
                    MetaFoxConstant::FILE_REMOVE_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS,
                    MetaFoxConstant::FILE_CREATE_STATUS, MetaFoxConstant::FILE_NEW_STATUS,
                ]),
            ],
            'attached_photos.*.temp_file' => [
                'required_if:attached_photos.*.status,create', 'numeric',
                new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'attached_photos.*.file_type' => [
                'required_if:attached_photos.*.status,create', 'string', new AllowInRule(
                    ['photo'],
                    __p('sevent::phrase.the_attached_photos_are_invalid')
                ),
            ],
        ];
        /**@deprecated V5.1.8 remove this */
        if (Settings::get('core.google.google_map_api_key') == null) {
            Arr::forget($rules, 'location');
            Arr::set($rules, 'location_name', ['required_if:is_online,0', 'nullable', 'string']);
        }
        $rules            = $this->applyAttachmentRules($rules);
        $rules            = $this->applyTagRules($rules);
        $rules['captcha'] = Captcha::ruleOf('sevent.create_sevent');
        return $rules;
    }

    public function getEventDateRule()
    {
        return new DateEqualOrAfterRule(
            Carbon::now(),
            __p('event::phrase.the_event_time_should_be_greater_than_the_current_time')
        );
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        $data['is_draft'] = 0;
        if (isset($data['draft'])) {
            $data['is_draft'] = $data['draft'];
        }

        $data['temp_file'] = 0;
        if (isset($data['file']['temp_file'])) {
            $data['temp_file'] = $data['file']['temp_file'];
        }

        $data['host_temp_file'] = 0;
        if (isset($data['host_image']['temp_file'])) {
            $data['host_temp_file'] = $data['host_image']['temp_file'];
        }

        if (isset($data['start_date'])) {
            $data['start_date'] = Carbon::parse($data['start_date'])->setTimezone('UTC')->toDateTimeString();
        }

        if (isset($data['end_date'])) {
            $data['end_date'] = Carbon::parse($data['end_date'])->setTimezone('UTC')->toDateTimeString();
        }

        if (empty($data['owner_id'])) {
            $data['owner_id'] = 0;
        }

        if (array_key_exists('tags', $data)) {
            $data['tags'] = parse_input()->extractResourceTopic($data['tags']);
        }
        
        $isOnlineEvent = Arr::get($data, 'is_online', 0);
        $data = array_merge($data, [
            'location_name'      => $isOnlineEvent ? null : ($data['location']['address'] ?? null),
            'location_latitude'  => $isOnlineEvent ? null : ($data['location']['lat'] ?? null),
            'location_longitude' => $isOnlineEvent ? null : ($data['location']['lng'] ?? null),
            'country_iso'        => $isOnlineEvent ? null : ($data['location']['short_name'] ?? null),
        ]);

        unset($data['location']);
        $data = $this->transformMobileFiles($data);

        return $data;
    }

    protected function transformMobileFiles(array $attributes): array
    {
        if (!count($attributes)) {
            return $attributes;
        }

        $photos = Arr::get($attributes, 'attached_photos');

        if (!is_array($photos)) {
            return $attributes;
        }

        $photos = array_map(function ($photo) {
            if (Arr::get($photo, 'status') != MetaFoxConstant::FILE_NEW_STATUS) {
                return $photo;
            }

            Arr::set($photo, 'status', MetaFoxConstant::FILE_CREATE_STATUS);

            return $photo;
        }, $photos);

        Arr::set($attributes, 'attached_photos', $photos);

        return $attributes;
    }
}
