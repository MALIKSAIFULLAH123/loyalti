<?php

namespace MetaFox\Comment\Http\Resources\v1\CommentAttachment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentAttachment;
use MetaFox\Comment\Models\CommentAttachment as Model;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\ResourcePermission as ACL;

/**
 * Class CommentAttachmentDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CommentAttachmentDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $image  = null;
        $params = null;

        switch ($this->resource->item_type) {
            case CommentAttachment::TYPE_LINK:
                $params = json_decode($this->resource->params, true);

                /*
                 * Hot fix for 5.1.18 for covering almost of apps which use html text as short description, the text still contains some characters has been encoded from client.
                 * We accepts to decode some special cases of user input like &lt; and &gt;
                 */
                if (is_array($params) && is_string($description = Arr::get($params, 'description')) && $description !== '') {
                    Arr::set($params, 'description', html_entity_decode($description));
                }

                break;
            case CommentAttachment::TYPE_GIF:
                $params = json_decode($this->resource->params, true);
                break;
            default:
                if ($this->resource->item_type) {
                    $item = ResourceGate::getItem($this->resource->item_type, $this->resource->item_id);
                    if ($item) {
                        $image = $item->images;
                    }
                }
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => Comment::ENTITY_TYPE,
            'resource_name' => $this->resource->entityType(),
            'item_id'       => $this->resource->item_id,
            'extra_type'    => $this->resource->item_type,
            'params'        => $params,
            'image'         => $image,
            'extra'         => $this->getExtra(),
        ];
    }

    protected function getExtra(): array
    {
        $context = user();
        $policy  = new CommentPolicy();

        return [
            ACL::CAN_DOWNLOAD => $policy->download($context, $this->resource),
        ];
    }
}
