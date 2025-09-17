<?php

namespace MetaFox\Core\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MetaFox\Core\Listeners\Traits\HandleResourceTextTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\PostBy;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\User\Models\User as UserModel;

class ModelCreatingListener
{
    use HandleResourceTextTrait;

    /**
     * @param Model $model
     *
     * @return void
     */
    public function handle(Model $model)
    {
        // Whenever a resource is going to be created, check it can be created in current context.
        if ($model instanceof Content && (!$model instanceof User || $model->hasContentPrivacy())) {
            if (!PrivacyPolicy::checkCreateResourceOnOwner($model)) {
                abort(403, __p('core::validation.unable_to_create_this_item_due_to_privacy'));
            }

            // When a content is being created on an owner other than its user, its privacy should be updated to follow owner's privacy rule
            if ($model instanceof HasPrivacy) {
                $owner = $model->owner;
                // Only when the content is created on owner
                if ($owner instanceof HasPrivacyMember) {
                    if ($owner instanceof PostBy) {
                        $model->privacy = $owner->getPrivacyPostBy();
                    }
                }
            }
        }

        if ($model instanceof Content) {
            $this->handleFeaturedAt($model);
        }

        if ($model instanceof ResourceText) {
            $this->handleResourceText($model);
        }
    }

    private function handleFeaturedAt(Content $model): void
    {
        if ($model instanceof HasFeature) {
            if (HasFeature::IS_FEATURED == $model->is_featured) {
                $model->featured_at = Carbon::now();
            }
        }
    }

    /**
     * This handler is for handling previllege cases where there are some users who
     * have permission to approve pending items.
     * Thus, those items which they creates should be approved after created.
     */
    private function handlePendingItem(Content $model): void
    {
        if (!$model instanceof Model) {
            return;
        }

        if ($model->isApproved()) {
            return;
        }

        $user = $model->user;
        if (!$user instanceof UserModel) {
            return;
        }

        // @todo: Can be changed to users have 'approve' policy?
        if ($user->hasSuperAdminRole()) {
            $model->fill(['is_approved' => 1]);
        }
    }
}
