<?php

namespace MetaFox\Announcement\Http\Requests\v1\Announcement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use MetaFox\Announcement\Rules\AnnouncementHideRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Announcement\Http\Controllers\Api\v1\AnnouncementController::store;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class HideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user() instanceof \MetaFox\Platform\Contracts\User;
    }

    public function rules(): array
    {
        $context = user();

        return [
            'announcement_id' => ['required', 'numeric', 'min:1', new AnnouncementHideRule($context)],
        ];
    }
}
