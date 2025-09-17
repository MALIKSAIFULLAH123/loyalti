<?php

namespace MetaFox\User\Http\Resources\v1\UserEntity;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Models\UserEntity as Model;

/**
 * Class UserEntityPreview.
 * @property Model $resource
 */
class UserEntityPreview extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $detail = $this->resource->detail;

        try {
            /** @var JsonResource | null $userPreview */
            $userPreview = app('events')->dispatch($detail->entityType() . '.get_user_preview', [$detail], true);
        } catch (Exception $e) {
            $userPreview = null;
        }

        return null !== $userPreview ? $userPreview->toArray($request) : [];
    }
}
