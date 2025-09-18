<?php

namespace MetaFox\Quiz\Http\Resources\v1\Result;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Quiz\Models\Result as Model;

/**
 * Class ResultItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ResultItem extends JsonResource
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
        $result       = $totalCorrect . '/' . $itemCount;
        if (Settings::get('quiz.show_success_as_percentage_in_result', true)) {
            $result = ($itemCount == 0 ? 0 : round(($totalCorrect / $itemCount) * 100, 2)) . '%';
        }

        return [
            'id'             => $this->resource->entityId(),
            'resource_name'  => $this->resource->entityType(),
            'index'          => $this->resource->quiz->entityId() . ':' . $this->resource->entityId(),
            'result_correct' => $result,
            'user'           => ResourceGate::user($this->resource->userEntity),
            'creation_date'  => $this->resource->created_at,
        ];
    }
}
