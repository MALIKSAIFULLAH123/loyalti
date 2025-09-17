<?php

namespace MetaFox\Blog\Http\Resources\v1\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Blog\Models\Blog;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class BlogPropertiesSchema.
 * @property ?Blog $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class BlogPropertiesSchema extends JsonResource
{
    use HasHashtagTextTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $user           = new UserPropertiesSchema($this->resource?->user);
        $userProperties = Arr::dot($user->toArray($request), 'user_');
        $userProperties = Arr::undot($userProperties);

        if (!$this->resource instanceof Blog) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        $shortDescription = $text = '';

        if ($this->resource?->blogText) {
            $shortDescription = parse_output()->getDescription($this->resource?->blogText?->text_parsed);
            $text             = $this->getTransformContent($this->resource?->blogText?->text_parsed);
            $text             = parse_output()->parseItemDescription($text);
        }

        return array_merge([
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'title'             => $this->resource->title,
            'description'       => $shortDescription,
            'text'              => $text,
            'image'             => $this->resource->image,
            'tags'              => $this->resource->tags,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'creation_date'     => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date' => Carbon::parse($this->resource->updated_at)->format('c'),
            'category_names'    => $this->resource->categories->pluck('name')->toArray(),
        ], $userProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                => null,
            'module_name'       => null,
            'title'             => null,
            'description'       => null,
            'text'              => null,
            'image'             => null,
            'tags'              => null,
            'link'              => null,
            'url'               => null,
            'creation_date'     => null,
            'modification_date' => null,
            'category_names'    => null,
        ];
    }
}
