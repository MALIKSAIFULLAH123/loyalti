<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumPostText;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class ForumPostPropertiesSchema.
 * @property ?ForumPost $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ForumPostPropertiesSchema extends JsonResource
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

        if (!$this->resource instanceof ForumPost) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        $resource = $this->resource;
        $content  = '';
        $postText = $resource->postText;

        if ($postText instanceof ForumPostText) {
            $content = parse_output()->parseItemDescription($postText->text_parsed);
        }

        return array_merge([
            'id'                => $resource->entityId(),
            'short_content'     => $resource->short_content,
            'content'           => $content,
            'url'               => $resource->toUrl(),
            'link'              => $resource->toLink(),
            'creation_date'     => $this->convertDate($resource->created_at),
            'modification_date' => $this->convertDate($resource->updated_at),
            'total_like'        => $resource->getTotalLike(),
            'total_attachment'  => $resource->getTotalAttachment(),
            'total_share'       => $resource->getTotalShare(),
        ], $userProperties);
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                => null,
            'short_content'     => null,
            'content'           => null,
            'url'               => null,
            'link'              => null,
            'creation_date'     => null,
            'modification_date' => null,
            'total_like'        => null,
            'total_attachment'  => null,
            'total_share'       => null,
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
