<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use MetaFox\Platform\Rules\PrivacyRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\v1\FeedController::update;
 * stub: api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends StoreRequest
{
    public function rules(): array
    {
        $rules = [
            'post_type'            => ['required'],
            'privacy'              => ['sometimes', new PrivacyRule()],
            'status_background_id' => ['sometimes', 'numeric', 'min:0'],

            // Post to.
            // sql injection fixed.
            'parent_item_id' => ['sometimes', 'integer', 'exists:user_entities,id'],
            'user_status'    => ['sometimes'],
        ];

        $rules = $this->applyLocationRules($rules);

        $rules = $this->applyTaggedFriendsRulesForEdit($rules);

        return $rules;
    }

    protected function isEdit(): bool
    {
        return true;
    }
}
