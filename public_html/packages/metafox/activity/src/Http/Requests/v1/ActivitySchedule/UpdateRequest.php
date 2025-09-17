<?php

namespace MetaFox\Activity\Http\Requests\v1\ActivitySchedule;

use Illuminate\Support\Carbon;
use MetaFox\Activity\Http\Requests\v1\Feed\StoreRequest;
use MetaFox\Core\Rules\DateEqualOrAfterRule;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\v1\ActivityScheduleController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'post_type'            => ['required'],
            'privacy'              => ['sometimes', new PrivacyRule()],
            'status_background_id' => ['sometimes', 'numeric', ' min:1'],

            // Post to.
            'parent_item_id'       => ['sometimes', 'integer', new ExistIfGreaterThanZero('exists:user_entities,id')],
            // Post as parent
            'post_as_parent'       => ['sometimes', 'boolean'],
            'user_status'          => ['sometimes'],
            // Schedule post
            'schedule_time'        => ['required', 'date', new DateEqualOrAfterRule(Carbon::now())],
        ];

        $rules = $this->applyLocationRules($rules);

        return $this->applyTaggedFriendsRules($rules);
    }

    public function messages(): array
    {
        return [
            'schedule_time.*' => __p('activity::validation.schedule_time_must_be_a_date_after_or_equal_to_date', [
                'date' => Carbon::now()->setTimezone(MetaFox::clientTimezone())->format('M d, Y g:i A'),
            ]),
        ];
    }

    protected function isEdit(): bool
    {
        return true;
    }

    protected function isEditSchedule(): bool
    {
        return true;
    }
}
