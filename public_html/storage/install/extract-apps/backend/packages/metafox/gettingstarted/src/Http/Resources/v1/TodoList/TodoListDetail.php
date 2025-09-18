<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Enumerable;
use MetaFox\GettingStarted\Models\TodoList;
use MetaFox\GettingStarted\Support\Traits\TodoListTrait;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class TodoListDetail.
 * @property TodoList $resource
 * @ignore
 * @codeCoverageIgnore
 */
class TodoListDetail extends JsonResource
{
    use HasHashtagTextTrait;
    use TodoListTrait;

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
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'getting-started',
            'resource_name' => $this->resource->entityType(),
            'title'         => $this->resource->title,
            'description'   => $this->getDescription(),
            'attach_images' => $this->getAttachedImages(),
            'images'        => $this->resource->images,
            'is_done'       => $this->isDone($this->resource->entityId(), user()->id),
            'ordering'      => $this->resource->ordering,
            'resolution'    => $this->resource->resolution,
            'created_at'    => $this->resource->created_at,
            'updated_at'    => $this->resource->updated_at,
        ];
    }

    protected function getAttachedImages(): ?Enumerable
    {
        $attachedImages = null;

        if ($this->resource->images->count()) {
            $attachedImages = $this->resource->images->map(function ($photo) {
                return ResourceGate::asItem($photo, null);
            });
        }

        return $attachedImages;
    }

    protected function getDescription(): ?string
    {
        $text = null;

        if (null !== $this->resource->description) {
            $text = $this->resource->description->text_parsed;

            $text = $this->getTransformContent($text);

            $text = parse_output()->parseItemDescription($text);
        }

        return $text;
    }
}
