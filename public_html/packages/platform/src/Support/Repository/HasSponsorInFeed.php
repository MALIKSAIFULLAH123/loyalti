<?php

namespace MetaFox\Platform\Support\Repository;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Trait HasSponsorInFeed.
 */
trait HasSponsorInFeed
{
    public function unsponsorInFeed(ContractUser $context, int $id): bool
    {
        $resource = $this->find($id);

        if (!$resource instanceof Content) {
            return false;
        }

        gate_authorize($context, 'unsponsorInFeed', $resource, 0);

        app('events')->dispatch('advertise.sponsor.unsponsor_feed', [$context, $resource]);

        return true;
    }

    /**
     * @param ContractUser $context
     * @param int          $id
     * @param int          $newValue
     *
     * @return bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function sponsorInFeed(ContractUser $context, int $id, int $newValue): bool
    {
        /*
         * TODO: remove after implement endpoint for unsponsor
         */
        if ($newValue == 0) {
            return $this->unsponsorInFeed($context, $id);
        }

        $resource = $this->find($id);

        if (!$resource instanceof Content) {
            return false;
        }

        if ($resource instanceof HasPrivacy && $resource->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }

        gate_authorize($context, 'sponsorInFeed', $resource, 1);

        app('events')->dispatch('advertise.sponsor.sponsor_feed_free', [$context, $resource]);

        return true;
    }

    /**
     * @param  Content $model
     * @return bool
     */
    public function isFeedSponsored(Content $model): bool
    {
        return $model->isSponsoredInFeed();
    }
}
