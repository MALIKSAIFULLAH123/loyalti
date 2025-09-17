<?php

namespace MetaFox\AntiSpamQuestion\Http\Requests\v1\Question\Admin;

use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\AntiSpamQuestion\Http\Controllers\Api\v1\QuestionAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest
 */
class UpdateRequest extends StoreRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules()
    {
        return array_merge(
            parent::rules(), [
            'answers.*.id'     => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:asq_answers,id')],
            'answers.*.status' => ['sometimes', 'nullable', 'string', new AllowInRule([
                MetaFoxConstant::FILE_NEW_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS,
                MetaFoxConstant::FILE_REMOVE_STATUS,
            ])],
        ]);
    }
}
