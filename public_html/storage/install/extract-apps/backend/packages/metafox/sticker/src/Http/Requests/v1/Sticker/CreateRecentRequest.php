<?php

namespace MetaFox\Sticker\Http\Requests\v1\Sticker;

use Illuminate\Foundation\Http\FormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Sticker\Http\Controllers\Api\v1\StickerController::createRecent;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class CreateRecentRequest.
 */
class CreateRecentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sticker_id' => ['required', 'numeric', 'exists:stickers,id'],
        ];
    }
}
