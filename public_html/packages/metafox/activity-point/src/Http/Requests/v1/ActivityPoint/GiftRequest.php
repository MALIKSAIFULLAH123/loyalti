<?php

namespace MetaFox\ActivityPoint\Http\Requests\v1\ActivityPoint;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use MetaFox\ActivityPoint\Repositories\PointStatisticRepositoryInterface;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\ActivityPoint\Http\Controllers\Api\v1\ActivityPointController::gift
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class GiftRequest.
 */
class GiftRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        $context      = user();

        $currentPoint = resolve(PointStatisticRepositoryInterface::class)->viewStatistic($context, $context->entityId())->available_points;

        return [
            'points' => ['required', 'numeric', 'min:1', "max:$currentPoint"],
        ];
    }
}
