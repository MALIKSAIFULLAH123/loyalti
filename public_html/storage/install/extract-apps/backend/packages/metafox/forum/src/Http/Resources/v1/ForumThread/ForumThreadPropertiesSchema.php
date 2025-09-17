<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class ForumThreadPropertiesSchema.
 * @property ?ForumThread $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ForumThreadPropertiesSchema extends JsonResource
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

        if (!$this->resource instanceof ForumThread) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        $resource    = $this->resource;
        $description = null;

        if (null !== $resource->description) {
            $description = $this->getTransformContent($resource->description->text_parsed);
            $description = parse_output()->parseItemDescription($description);
        }

        $title = ban_word()->clean($resource->toTitle());
        $title = ban_word()->parse($title);

        return array_merge([
            'id'                => $resource->entityId(),
            'title'             => $title,
            'description'       => $description,
            'short_description' => $resource->short_description,
            'tags'              => $resource->getTags(),
            'total_comment'     => $resource->getTotalPost(),
            'total_view'        => $resource->getTotalView(),
            'total_like'        => $resource->getTotalLike(),
            'total_share'       => $resource->getTotalShare(),
            'link'              => $resource->toLink(),
            'url'               => $resource->toUrl(),
            'creation_date'     => $this->convertDate($resource->getCreatedAt()),
            'modification_date' => $this->convertDate($resource->getUpdatedAt()),
        ], $userProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                => null,
            'title'             => null,
            'description'       => null,
            'short_description' => null,
            'tags'              => null,
            'total_comment'     => null,
            'total_view'        => null,
            'total_like'        => null,
            'total_share'       => null,
            'link'              => null,
            'url'               => null,
            'creation_date'     => null,
            'modification_date' => null,
        ];
    }

    protected function convertDate(?string $date): ?string
    {
        if (null == $date) {
            return null;
        }

        return Carbon::parse($date)->format('c');
    }
}
