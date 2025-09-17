<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Activity\Rules\MaxlengthPostStatusRule;
use MetaFox\Activity\Traits\HasCheckinTrait;
use MetaFox\Activity\Traits\HasTaggedFriendTrait;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Core\Rules\DateEqualOrAfterRule;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\v1\FeedController::store;
 * stub: api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    use HasTaggedFriendTrait;
    use HasCheckinTrait;

    /***********************************************************************
     *
     *  {
     * "user_status" : "New status ",
     * "post_type" : "activity_post",
     * // "tagged_friends" : {{friend_id}},
     * "location" : {
     * "address" : "Notre Dame Cathedral of Saigon 2",
     * "lat" : 10.7797908,
     * "lng" : 106.6968302
     * },
     * "parent_item_type":"",
     * "parent_item_id":"",
     * "post_as_parent":0,
     * "privacy" : 0
     * }
     *
     *
     *
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function rules(): array
    {
        $rules = [
            'post_type'            => ['required'],
            'privacy'              => ['sometimes', new PrivacyRule()],
            'status_background_id' => ['sometimes', 'numeric', ' min:1'],

            // Post to.
            'parent_item_id'       => ['sometimes', new ExistIfGreaterThanZero('exists:user_entities,id')],
            // Post as parent
            'post_as_parent'       => ['sometimes', 'boolean'],
            'user_status'          => ['sometimes', new MaxlengthPostStatusRule()],
            // Schedule post
            'schedule_time'        => ['sometimes', 'date', 'nullable', new DateEqualOrAfterRule(Carbon::now())],
        ];

        $rules = $this->applyLocationRules($rules);

        $rules = $this->applyTaggedFriendsRules($rules);

        return $rules;
    }

    public function messages(): array
    {
        return [
            'schedule_time.*' => __p('activity::validation.schedule_time_must_be_a_date_after_or_equal_to_date', [
                'date' => Carbon::now()->setTimezone(MetaFox::clientTimezone())->format('M d, Y g:i A'),
            ]),
        ];
    }

    /**
     * @throws AuthenticationException
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $postType = Arr::get($data, 'post_type');

        $formData = $this->validatedPostType($postType);

        if (count($formData)) {
            $data = array_merge($data, $formData);
        }

        if (!$this->isEnableTagFriends()) {
            Arr::forget($data, ['tagged_in_photo', 'tagged_friends']);
        }

        return $this->transform($data);
    }

    protected function validatedPostType(string $postType): array
    {
        $driver = resolve(DriverRepositoryInterface::class)
            ->getDriver(Constants::DRIVER_TYPE_FORM, $postType . '.feed_form', 'web');

        $form = app()->make($driver, [
            'resource'       => null,
            'isEdit'         => $this->isEdit(),
            'isEditSchedule' => $this->isEditSchedule(),
        ]);

        $response = [];

        if (is_object($form) && method_exists($form, 'validated')) {
            $formData = app()->call([$form, 'validated']);

            if (is_array($formData)) {
                $response = $formData;
            }
        }

        return $response;
    }

    protected function isEdit(): bool
    {
        return false;
    }

    protected function isEditSchedule(): bool
    {
        return false;
    }

    /**
     * @param array<string,           mixed> $data
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function transform(array $data): array
    {
        $data = $this->transformPostAsParent($data);

        if (!Arr::has($data, 'privacy')) {
            Arr::set($data, 'privacy', MetaFoxPrivacy::EVERYONE);
        }

        $data['list'] = [];

        if (is_array($data['privacy'])) {
            $data = array_merge($data, [
                'list'    => Arr::get($data, 'privacy'),
                'privacy' => MetaFoxPrivacy::CUSTOM,
            ]);
        }

        if ($this->isEnableCheckin()) {
            $data['location_address']   = Arr::get($data, 'location.full_address');
            $data['location_name']      = Arr::get($data, 'location.address');
            $data['location_latitude']  = Arr::get($data, 'location.lat');
            $data['location_longitude'] = Arr::get($data, 'location.lng');
        }

        unset($data['location']);

        if ($this->isEnableTagFriends()) {
            $data['tagged_friends'] = $this->handleTaggedFriend($data);
        }

        $userStatus = Arr::get($data, 'user_status');

        if (null === $userStatus) {
            Arr::set($data, 'user_status', '');
        }

        if ($data['user']->entityId() != $data['owner']->entityId()) {
            $privacy = UserPrivacy::getProfileSetting($data['owner']->entityId(), 'feed:view_wall');
            Arr::set($data, 'privacy', $privacy);
        }

        return $data;
    }

    protected function transformPostAsParent(array $data): array
    {
        $user = $owner = user();

        Arr::set($data, 'owner', $owner);

        Arr::set($data, 'user', $user);

        if (!Arr::has($data, 'parent_item_id')) {
            return $data;
        }

        // Login as page
        if ($data['parent_item_id'] == $user->entityId()) {
            return $data;
        }

        if ($this->isEdit()) {
            unset($data['post_as_parent']);

            return $data;
        }

        $owner = UserEntity::getById($data['parent_item_id'])->detail;

        Arr::set($data, 'owner', $owner);

        $postAsParent = Arr::get($data, 'post_as_parent', 0);

        if (!$postAsParent) {
            return $data;
        }

        $policy = PolicyGate::getPolicyFor(get_class($owner));

        if (null === $policy) {
            return $data;
        }

        if (!method_exists($policy, 'postAsParent')) {
            return $data;
        }

        policy_authorize(get_class($policy), 'postAsParent', $user, $owner);

        Arr::set($data, 'user', $owner);

        unset($data['post_as_parent']);

        return $data;
    }
}
