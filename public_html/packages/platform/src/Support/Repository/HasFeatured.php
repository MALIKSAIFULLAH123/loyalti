<?php

namespace MetaFox\Platform\Support\Repository;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPolicy;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Trait HasFeatured.
 */
trait HasFeatured
{
    /**
     * @deprecated Remove on 5.2.0
     * @param User $context
     * @param int  $id
     * @param int  $feature
     *
     * @return bool
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function feature(User $context, int $id, int $feature): bool
    {
        $model = $this->find($id);

        if ($model instanceof HasPrivacy && $model->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }

        if ($model instanceof HasPolicy) {
            gate_authorize($context, 'feature', $model, $model, $feature);
        }

        return $model->update(['is_featured' => $feature]);
    }

    /**
     * @param Content $model
     *
     * @return bool
     */
    public function isFeature(Content $model): bool
    {
        if (!$model instanceof HasFeature) {
            return false;
        }

        return $model->is_featured == 1;
    }

    public function featureFree(User $context, int $id): bool
    {
        $model = $this->find($id);

        if (!$model instanceof Content) {
            return false;
        }

        if ($model instanceof HasPrivacy && $model->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }

        gate_authorize($context, 'featureFree', $model, $model);

        $result = $model->update(['is_featured' => 1]);

        if (!$result) {
            return false;
        }

        app('events')->dispatch('featured.item.feature_free', [$context, $model]);

        return true;
    }

    public function unfeature(User $context, int $id): bool
    {
        $model = $this->find($id);

        if (!$model instanceof Content) {
            return false;
        }

        if ($model instanceof HasPrivacy && $model->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }

        gate_authorize($context, 'unfeature', $model, $model);

        $result = $model->update(['is_featured' => 0]);

        if (!$result) {
            return false;
        }

        app('events')->dispatch('featured.item.unfeature', [$context, $model]);

        return true;
    }
}
