<?php

namespace MetaFox\Comment\Policies;

use Illuminate\Support\Arr;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentAttachment;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\ActionOnResourcePolicyInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class CommentPolicy.
 * @method viewApprove(User $user, ?Content $resource = null)
 */
class CommentPolicy implements
    ActionOnResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    protected string $type = Comment::ENTITY_TYPE;

    public function getEntityType(): string
    {
        return Comment::ENTITY_TYPE;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        if (!$this->viewApprove($user, $resource)) {
            return false;
        }

        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if ($owner == null) {
            return false;
        }

        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'comment.view_browse_comments')) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof HasTotalComment) {
            return false;
        }

        if ($resource instanceof Comment && !$resource->isApproved()) {
            return false;
        }

        return $this->commentItem($resource?->entityType(), $user, $resource);
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('comment.moderate')) {
            return true;
        }

        if (!$resource instanceof ActionEntity) {
            return false;
        }

        if ($this->updateOwnItem($user, $resource)) {
            return true;
        }

        if ($user->hasPermissionTo('comment.update')) {
            return $user->entityId() == $resource->userId();
        }

        return false;
    }

    public function updateOwnItem(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof ActionEntity) {
            return false;
        }
        $item = $resource->item;

        if (!$user->hasPermissionTo('comment.update_on_own_item')) {
            if ($user->hasPermissionTo('comment.update')) {
                return $user->entityId() == $resource->userId();
            }

            return false;
        }

        return $user->entityId() == $item->userId();
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('comment.moderate')) {
            return true;
        }

        if (!$resource instanceof ActionEntity) {
            return false;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof ActionEntity) {
            return false;
        }

        $item = $resource->item;

        if (null == $item) {
            return false;
        }

        /*
         * This condition is for user that create this comment
         */
        if ($user->entityId() == $resource->userId() && $user->hasPermissionTo('comment.delete')) {
            return true;
        }

        if ($user->entityId() == $item->userId() && $user->hasPermissionTo('comment.delete_on_own_item')) {
            return true;
        }

        // todo performance slow.
        $owner = $item->owner;

        if ($owner instanceof HasPrivacyMember) {
            return $this->checkModeratorSetting($user, $owner, 'remove_post_and_comment_on_post');
        }

        return false;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function share(User $user, ?Content $resource = null): bool
    {
        return false;
    }

    public function viewHistory(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Comment) {
            return false;
        }

        return (bool) $resource->is_edited;
    }

    public function hide(User $context, ?Content $resource = null): bool
    {
        if (null == $resource) {
            return false;
        }

        if ($context->entityId() == $resource->userId()) {
            return false;
        }

        if ($context->entityId() == $resource->ownerId()) {
            return false;
        }

        return $context->hasPermissionTo('comment.hide');
    }

    public function hideGlobal(User $context, ?Content $resource = null): bool
    {
        if (null === $resource) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        $user = $resource->user;

        $owner = $resource->owner;

        if (null === $owner) {
            return false;
        }

        if (null === $user) {
            return false;
        }

        if ($user->entityId() == $context->entityId()) {
            return false;
        }

        return $owner->entityId() == $context->entityId();
    }

    public function removeLinkPreview(User $user, Entity $resource): bool
    {
        if (null === $resource->commentAttachment) {
            return false;
        }

        if ($resource->commentAttachment->item_type != CommentAttachment::TYPE_LINK) {
            return false;
        }

        $params = json_decode($resource->commentAttachment->params, true);

        if (!is_array($params)) {
            return false;
        }

        if (Arr::get($params, 'is_hidden')) {
            return false;
        }

        if (!$this->update($user, $resource)) {
            return false;
        }

        return true;
    }

    public function commentItem(string $entityType, User $user, $resource, $newValue = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource instanceof HasTotalComment) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        // User of this role can comment
        if (!$user->hasPermissionTo('comment.comment')) {
            return false;
        }

        if (!$this->checkPermissionOnItem($user, $resource)) {
            return false;
        }

        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return false;
        }

        if (!$owner->isApproved()) {
            return false;
        }

        if (app('events')->dispatch('comment.owner.can_comment_item', [$user, $owner], true)) {
            return true;
        }

        if ($owner->entityId() != $user->entityId()) {
            if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                return false;
            }

            if (!PrivacyPolicy::checkCreateOnOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function like(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource instanceof HasTotalLike) {
            return false;
        }

        if ($resource instanceof Comment && !$resource->isApproved()) {
            return false;
        }

        if (!$user->hasPermissionTo('like.create')) {
            return false;
        }

        $item = $resource->item;

        if (!$item instanceof Content) {
            return false;
        }

        return $this->invokePolicyMethod($user, $item, 'like');
    }

    protected function checkPermissionOnItem(User $user, Content $resource): bool
    {
        $resourceEntityType = $resource->entityType();
        $permissionName     = "$resourceEntityType.comment";

        try {
            Permission::findByName($permissionName);

            return $user->hasPermissionTo($permissionName);
        } catch (\Exception $e) {
            return $this->invokePolicyMethod($user, $resource, 'comment');
        }
    }

    private function invokePolicyMethod(User $user, Content $resource, string $policyMethod): bool
    {
        $policy = PolicyGate::getPolicyFor(get_class($resource));

        if (!is_object($policy)) {
            return true;
        }

        if (!method_exists($policy, $policyMethod)) {
            return true;
        }

        return $policy->$policyMethod($user, $resource);
    }

    public function download(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof CommentAttachment) {
            return false;
        }

        if ($resource->itemType() != CommentAttachment::TYPE_FILE) {
            return false;
        }

        if (!$user->hasPermissionTo('comment.download_attachment')) {
            return false;
        }

        return true;
    }

    public function addAttachment(User $user, array $attributes): bool
    {
        if (Arr::get($attributes, 'sticker_id', 0) > 0) {
            return Settings::get('comment.enable_sticker');
        }

        if (Arr::get($attributes, 'photo_id', 0) > 0) {
            return Settings::get('comment.enable_photo');
        }

        if (Arr::get($attributes, 'giphy_gif_id', '') != '') {
            return Settings::get('comment.enable_giphy');
        }

        return true;
    }

    public function updateAttachment(User $user, ?Entity $resource, array $attributes): bool
    {
        if (!$resource instanceof Comment) {
            return false;
        }

        $commentAttachment = $resource->commentAttachment;
        $stickerId         = Arr::get($attributes, 'sticker_id', 0);
        $photoId           = Arr::get($attributes, 'photo_id', 0);
        $giphyGifId        = Arr::get($attributes, 'giphy_gif_id', '');

        if (!$commentAttachment instanceof CommentAttachment) {
            return $this->addAttachment($user, $attributes);
        }

        if ($stickerId > 0 && $stickerId != $commentAttachment->item_id) {
            return Settings::get('comment.enable_sticker');
        }

        if ($photoId > 0 && $photoId != $commentAttachment->item_id) {
            return Settings::get('comment.enable_photo');
        }

        $params = json_decode($commentAttachment->params);
        if ($giphyGifId != '' && property_exists($params, 'giphy_gif_id') && $giphyGifId != $params->giphy_gif_id) {
            return Settings::get('comment.enable_giphy');
        }

        return true;
    }

    public function translate(User $user): bool
    {
        if (!$user->hasPermissionTo('comment.translate')) {
            return false;
        }

        return true;
    }
}
