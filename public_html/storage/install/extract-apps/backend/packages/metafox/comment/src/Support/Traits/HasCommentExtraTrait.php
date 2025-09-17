<?php

namespace MetaFox\Comment\Support\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Support\ResourcePermission;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\ResourcePermission as ACL;
use MetaFox\Platform\Support\AppSetting\ResourceExtraTrait;

/**
 * Trait CommentExtraTrait.
 * @property Comment $resource
 */
trait HasCommentExtraTrait
{
    use ResourceExtraTrait;

    /**
     * @param Entity|null $item
     * @return array
     * @throws AuthenticationException
     */
    protected function getExtra(): array
    {
        $policy = new CommentPolicy();

        $context = user();

        $item = request()->get('comment_item_model');

        if (!$item instanceof Content) {
            $item = $this->resource->item;
        }

        $canComment = $policy->create($context, $this->resource) && $context->can('comment', [$item, $item]);
        return [
            ACL::CAN_VIEW                               => $policy->view($context, $this->resource),
            ACL::CAN_ADD                                => $canComment,
            ACL::CAN_EDIT                               => $policy->update($context, $this->resource),
            ACL::CAN_DELETE                             => $policy->delete($context, $this->resource),
            ACL::CAN_LIKE                               => call_user_func([$policy, 'like'], $context, $this->resource),
            ACL::CAN_VIEW_REACTION                      => $this->canViewReaction($context, $this->resource),
            ACL::CAN_APPROVE                            => $policy->approve($context, $this->resource),
            ACL::CAN_REPORT                             => $policy->reportItem($context, $this->resource),
            ACL::CAN_COMMENT                            => $canComment,
            ResourcePermission::CAN_HIDE                => $policy->hide($context, $this->resource),
            ResourcePermission::CAN_HIDE_GLOBAL         => $policy->hideGlobal($context, $this->resource),
            ResourcePermission::CAN_VIEW_HISTORIES      => $policy->viewHistory($context, $this->resource),
            ResourcePermission::CAN_REMOVE_LINK_PREVIEW => $policy->removeLinkPreview($context, $this->resource),
        ];
    }
}
