<?php

namespace MetaFox\Chat\Traits;

use MetaFox\Chat\Http\Resources\v1\Message\MessageAttachmentCollection;
use MetaFox\Chat\Repositories\MessageRepositoryInterface;
use MetaFox\Platform\ResourcePermission as ACL;

trait MessageTraits
{
    public function getPermissions(): array
    {
        $context = user();

        $resource = $this->resource;

        return [
            ACL::CAN_EDIT   => $context->can('update', [$resource, $resource]),
            ACL::CAN_DELETE => $context->can('delete', [$resource, $resource]),
        ];
    }

    protected function normalizeReactions(string|null $reactions)
    {
        if ($reactions == null) {
            return null;
        }

        $reactions = json_decode($reactions, true);

        return resolve(MessageRepositoryInterface::class)->normalizeReactions($reactions);
    }

    protected function processExtraMessage($extra)
    {
        if ($extra == null) {
            return null;
        }
        $extra                = json_decode($extra, true);
        $message              = resolve(MessageRepositoryInterface::class)->find($extra['id']);
        $extra['type']        = $message->type;
        $extra['attachments'] = new MessageAttachmentCollection($message->attachments);

        return $extra;
    }
}
