<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Traits\IsPrivacyItemTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;
use MetaFox\Platform\Contracts\Membership;
use MetaFox\Platform\Contracts\PrivacyList;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\MetaFoxPrivacy;
use Prettus\Validator\Exceptions\ValidatorException;

class ModelCreatedListener
{
    use IsPrivacyItemTrait;

    /**
     * @param Model $model
     *
     * @return void
     * @throws ValidatorException
     */
    public function handle(Model $model)
    {
        if ($model instanceof PrivacyList) {
            $this->handlePrivacyList($model);
        }

        // Create privacy members
        if ($model instanceof IsPrivacyItemInterface) {
            $this->handlePrivacyItemForCreated($model);
        }

        // Handle model created if resource then insert into core_privacy_streams.
        if ($model instanceof Content) {
            $this->handlePrivacyStream($model);
        }

        if ($model instanceof Membership) {
            $this->handlePrivacyStreamForMembership($model);
        }

        if ($model instanceof ResourceText) {
            app('events')->dispatch('search.updated', [$model->resource]);

            $this->handleResourceText($model);
        }
    }

    private function handlePrivacyList(PrivacyList $model): void
    {
        $payload = $model->toPrivacyLists();

        foreach ($payload as $data) {
            $privacyData = $this->privacyRepository()->create($data);

            // Automatically give owner_id (who the privacy belong to) own its privacy.
            // Privacy network has user_id = 0, skip.
            if ($data['user_type'] === MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE) {
                continue;
            }
            $this->privacyMemberRepository()->create([
                'user_id'    => $privacyData->owner_id,
                'privacy_id' => $privacyData->privacy_id,
            ]);
        }
    }

    /**
     * @throws ValidatorException
     */
    private function handlePrivacyStream(Content $model): void
    {
        $this->privacyRepository()->forceCreatePrivacyStream($model);
    }

    /**
     * @throws ValidatorException
     */
    private function handlePrivacyStreamForMembership(Membership $model): void
    {
        $privacyUidList = $this->privacyRepository()->getPrivacyIdsForMembership($model);

        if (!empty($privacyUidList)) {
            $privacyStreams = array_map(function ($privacyId) use ($model) {
                return [
                    'privacy_id' => $privacyId,
                    'item_id'    => $model->entityId(),
                    'item_type'  => $model->entityType(),
                ];
            }, $privacyUidList);
            $this->privacyStreamRepository()->createMany($privacyStreams);
        }
    }

    private function handleResourceText(ResourceText $model): void
    {
        $resource = $model->resource;
        if ($resource instanceof HasHashTag) {
            app('events')->dispatch('hashtag.create_hashtag', [$resource->user, $resource, $model->text_parsed], true);
        }
    }
}
