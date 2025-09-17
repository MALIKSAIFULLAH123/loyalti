<?php

namespace MetaFox\Comment\Http\Resources\v1\Pending\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentAttachment;
use MetaFox\Comment\Traits\HasTransformContent;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\ResourcePermission as ACL;

/**
 * Class PendingItem.
 * @property Comment $resource
 */
class PendingItem extends JsonResource
{
    use HasTransformContent;

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
        $item      = $this->resource->item;
        $itemAlias = getAliasByEntityType($item->entityType());
        $package   = app('core.packages')->getPackageByAlias($itemAlias);

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'user_name'     => $this->resource->userEntity?->display_name,
            'user_link'     => $this->resource->userEntity?->toUrl(),
            'item_name'     => htmlspecialchars_decode($item->toTitle()),
            'item_link'     => $item->toLink(),
            'item_type'     => $package->label,
            'date'          => $this->resource->created_at,
            'link'          => $this->resource->toFullLink(),
            'text'          => $this->handleText(),
            'extra'         => $this->getExtra(),
        ];
    }

    protected function getExtra(): array
    {
        $user = Auth::user();
        return [
            ACL::CAN_APPROVE => $user?->can('approve', [$this->resource, $this->resource]),
        ];
    }

    protected function handleText(): string
    {
        $text       = $this->getTransformContent(true);
        $attachment = $this->resource->commentAttachment;

        if (!$attachment instanceof CommentAttachment) {
            return $text;
        }

        $imageLink = $this->getImageLink($attachment);

        if (empty($imageLink)) {
            return $text;
        }

        return $this->formatTextWithImage($text, $imageLink);
    }

    protected function getImageLink(CommentAttachment $attachment): string
    {
        $item = ResourceGate::getItem($attachment->item_type, $attachment->item_id);

        return Arr::get($item?->images, 'origin', '');
    }

    protected function formatTextWithImage(string $text, string $imageLink): string
    {
        $imageTag = sprintf("<img src='%s'/>", $imageLink);

        return empty($text) ? $imageTag : $text . '<br/>' . $imageTag;
    }
}
