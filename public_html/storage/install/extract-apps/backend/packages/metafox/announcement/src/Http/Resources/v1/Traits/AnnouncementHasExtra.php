<?php

namespace MetaFox\Announcement\Http\Resources\v1\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Announcement\Models\Announcement as Model;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission as ACL;
use MetaFox\Platform\Support\AppSetting\ResourceExtraTrait;

/**
 * @property Content $resource
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait AnnouncementHasExtra
{
    use ResourceExtraTrait;
    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getAnnouncementExtra(): array
    {
        $context  = user();
        $resource = $this->resource;

        if (!$resource instanceof Content) {
            return [];
        }
        /** @var \MetaFox\Announcement\Policies\AnnouncementPolicy $policy */
        $policy = PolicyGate::getPolicyFor(Model::class);

        return [
            ACL::CAN_VIEW          => $context->can('view', [$resource, $resource]),
            ACL::CAN_LIKE          => $context->can('like', [$resource, $resource]),
            ACL::CAN_COMMENT       => $context->can('comment', [$resource, $resource]),
            ACL::CAN_VIEW_COMMENT  => $this->viewComment($context, $resource),
            'can_close'            => $policy->close($context, $resource),
            ACL::CAN_VIEW_REACTION => $this->canViewReaction($context, $this->resource),
        ];
    }
}
