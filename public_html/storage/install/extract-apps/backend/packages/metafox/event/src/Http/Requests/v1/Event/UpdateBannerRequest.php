<?php

namespace MetaFox\Event\Http\Requests\v1\Event;

use MetaFox\Core\Rules\ImageRule;
use MetaFox\Event\Models\Event;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Storage\Rules\MaxFileUpload;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Event\Http\Controllers\Api\v1\EventController::update;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateBannerRequest.
 */
class UpdateBannerRequest extends StoreRequest
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
        return [
            'image'     => ['sometimes', new ImageRule(), new MaxFileUpload()],
            'temp_file' => ['sometimes', 'integer', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'position'  => ['sometimes', 'string'],
        ];
    }
}
