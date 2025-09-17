<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Interface HasBackGroundStatus.
 * @property int $status_background_id
 */
interface HasBackGroundStatus
{
    /**
     * @deprecated Remove in 5.1.13
     * @return array<string, mixed>|null
     */
    public function getBackgroundStatusImage(): ?array;

    /**
     * @return JsonResource|null
     */
    public function getBackgroundStatus(?int $bgStatusId = null): ?JsonResource;
}
