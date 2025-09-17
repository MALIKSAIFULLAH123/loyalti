<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Activity\Traits\HasCheckinTrait;
use MetaFox\Activity\Traits\HasTaggedFriendTrait;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Rules\PrivacyListValidator;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\PrivacyValidator;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * Class ShareRequest.
 * @ignore
 * @codeCoverageIgnore
 */
class ShareRequest extends FormRequest
{
    use HasTaggedFriendTrait;
    use HasCheckinTrait;
    use PrivacyRequestTrait;
    use HasFilterTagUserTrait;

    public const DEFAULT_POST_TYPE = 'wall';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'user_status'       => ['sometimes', 'nullable', 'string'],
            'post_type'         => ['sometimes', 'string'],
            'item_id'           => ['required', 'numeric'],
            'item_type'         => ['required', 'string'],
            'context_item_id'   => ['sometimes', 'numeric'],
            'context_item_type' => ['sometimes', 'string'],
            'type_id'           => ['sometimes', 'string'],
            'post_content'      => ['sometimes', 'nullable', 'string'],
            'parent_feed_id'    => ['sometimes', 'numeric', 'exists:activity_feeds,id'],
            'privacy'           => ['sometimes', new PrivacyRule()],
        ];

        $rules = $this->applyLocationRules($rules);

        $rules = $this->applyTaggedFriendsRules($rules);

        $rules = $this->applyTargetRules($rules);

        return $rules;
    }

    protected function applyTargetRules(array $rules): array
    {
        $postType = $this->input('post_type');

        $ownerRules = app('events')->dispatch('activity.share.rules', [$postType], true);

        if (is_array($ownerRules) && count($ownerRules)) {
            $rules = array_merge($rules, $ownerRules);
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data = $this->handlePrivacy($data);

        $data['content'] = '';

        if (isset($data['post_content'])) {
            $data['content'] = $data['post_content'];
            unset($data['post_content']);
        }

        if (!isset($data['parent_feed_id'])) {
            $data['parent_feed_id'] = 0;
        }

        if (!isset($data['type_id'])) {
            $data['type_id'] = $data['item_type'];
        }

        if (!empty($data['location'])) {
            if ($this->isEnableCheckin()) {
                $data['location_address']   = Arr::get($data, 'location.full_address');
                $data['location_name']      = Arr::get($data, 'location.address');
                $data['location_latitude']  = Arr::get($data, 'location.lat');
                $data['location_longitude'] = Arr::get($data, 'location.lng');
            }

            unset($data['location']);
        }

        if ($this->isEnableTagFriends()) {
            $data['tagged_friends'] = $this->handleTaggedFriend($data);
        }

        $prepared = app('events')->dispatch('activity.share.data_preparation', [$data['post_type'] ?? self::DEFAULT_POST_TYPE, $data], true);

        if (is_array($prepared) && count($prepared)) {
            $data = $prepared;
        }

        return $data;
    }
}
