<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Foxexpert\Sevent\Models\Sevent;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Platform\Facades\Settings;
/**
 * @property Sevent $resource
 */
class FeedEmbed extends JsonResource
{
    use HasStatistic;

    public function toArray($request)
    {
        $shortDescription = '';

        if ($this->resource->seventText) {
            $shortDescription = parse_output()->getDescription($this->resource->seventText->text_parsed);
        }

        $postOnOther   = $this->resource->userId() != $this->resource->ownerId();
        $ownerResource = null;

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => $this->resource->title,
            'description'       => $shortDescription,
            'image'             => $this->resource->images,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'parent_user'       => $ownerResource,
            'info'              => 'added_a_sevent',
            'privacy'           => $this->resource->privacy,
            'is_featured'       => $this->resource->is_featured,
            'is_sponsor'        => $this->resource->is_sponsor,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'statistic'         => $this->getStatistic(),
            'creation_date'     => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date' => Carbon::parse($this->resource->updated_at)->toISOString(),
        ];
    }
}
