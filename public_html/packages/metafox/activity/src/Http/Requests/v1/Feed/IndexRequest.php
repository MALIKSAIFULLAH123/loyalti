<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Support\Facades\User as UserFacades;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\User as UserSupport;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\v1\FeedController::index;
 * stub: api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class IndexRequest extends FormRequest
{
    // @todo should implement collect resource to feed composer.
    public const VIEW_ACTIVITY_POST = Post::ENTITY_TYPE;
    public const VIEW_MEDIA         = 'media';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function rules(): array
    {
        return [
            'q'                           => ['sometimes', 'string'],
            'page'                        => ['sometimes', 'numeric', 'min:1'],
            'limit'                       => ['sometimes', 'numeric', new PaginationLimitRule()],
            'user_id'                     => ['sometimes', 'integer', 'min:1', 'exists:user_entities,id'],
            'item_id'                     => ['sometimes', 'numeric'],
            'item_type'                   => ['sometimes', 'string'],
            'hashtag'                     => ['sometimes'],
            'last_feed_id'                => ['sometimes', 'numeric'],
            'related_comment_friend_only' => ['sometimes', 'in:0,1'],
            'view'                        => ['sometimes', 'string'],
            'from'                        => [
                'sometimes', 'string', new AllowInRule([Browse::VIEW_ALL, 'user', 'page', 'group']),
            ],
            'type_id'                     => [
                'sometimes',
                'string',
            ],
            'sort'                        => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
            'sort_type'                   => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSortType())],
            'status'                      => ['sometimes', 'string'],
            'is_preview_tag'              => ['sometimes', new AllowInRule([0, 1])],
            'has_pin_post'                => ['sometimes', new AllowInRule([0, 1])],
            'sponsored_feed_ids'          => ['sometimes', 'array'],
            'sponsored_feed_ids.*'        => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['user_id'])) {
            $data['user_id'] = null;
        }

        if (isset($data['q'])) {
            $data['hashtag'] = trim($data['q']);
            unset($data['q']);
        } else {
            $data['hashtag'] = null;
        }

        if (!isset($data['from']) || Browse::VIEW_ALL == $data['from']) {
            $data['from'] = null;
        }

        if (!isset($data['type_id']) || Browse::VIEW_ALL == $data['type_id']) {
            $data['type_id'] = null;
        }

        if (!isset($data['last_feed_id'])) {
            $data['last_feed_id'] = null;
        }

        if (!isset($data['sort'])) {
            $userId      = Arr::get($data, 'user_id', 0);
            $from        = UserSupport::KEY_SORT_FEED_VALUES_ON_HOME;
            $settingName = UserSupport::SORT_FEED_VALUES_SETTING;
            $values      = UserFacades::getReferenceValueByName(user(), $settingName);

            if ($userId > 0) {
                $profile = UserEntity::getById($userId)->detail;
                $from    = sprintf('%s.%s', $profile->moduleName(), $profile->entityType());
            }

            $data['sort'] = Arr::get($values, $from, Settings::get('feed.sort_default'));
        }

        if (!isset($data['sort_type'])) {
            $data['sort_type'] = Browse::SORT_TYPE_DESC;
        }

        if (!isset($data['is_preview_tag'])) {
            $data['is_preview_tag'] = 0;
        }

        if (!isset($data['has_pin_post'])) {
            $data['has_pin_post'] = 1;
        }

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE_SPECIAL_CASE;
        }

        if (!Arr::has($data, 'status')) {
            Arr::set($data, 'status', MetaFoxConstant::ITEM_STATUS_APPROVED);
        }

        return $data;
    }
}
