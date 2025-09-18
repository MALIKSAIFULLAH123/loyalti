<?php

namespace MetaFox\Quiz\Http\Resources\v1\Result;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Quiz\Http\Resources\v1\ResultDetail\ResultDetailItemCollection;
use MetaFox\Quiz\Models\Result as Model;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
|
*/

/**
 * Class ResultDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ResultDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $itemCount    = $this->resource->quiz->questions->count();
        $totalCorrect = $this->resource->total_correct;

        $result = $totalCorrect . '/' . $itemCount;
        if (Settings::get('quiz.show_success_as_percentage_in_result', true)) {
            $result = ($itemCount == 0 ? 0 : round(($totalCorrect / $itemCount) * 100, 2)) . '%';
        }

        return [
            'id'             => $this->resource->entityId(),
            'resource_name'  => $this->resource->entityType(),
            'user'           => ResourceGate::user($this->resource->userEntity),
            'result_correct' => $result,
            'user_result'    => new ResultDetailItemCollection($this->resource->items),
        ];
    }
}
