<?php

namespace MetaFox\Core\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Traits\IsPrivacyItemTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\MetaFoxPrivacy;
use Prettus\Validator\Exceptions\ValidatorException;

class ModelUpdatedListener
{
    use IsPrivacyItemTrait;

    /**
     * @param  Model              $model
     * @throws ValidatorException
     */
    public function handle(Model $model): void
    {
        // Handle model updated if resource then insert into core_privacy_streams.
        $this->handleUpdatePrivacyStream($model);

        if ($model instanceof ResourceText) {
            app('events')->dispatch('search.updated', [$model->resource]);
        }

        //recreate privacy member
        if ($model instanceof IsPrivacyItemInterface) {
            $this->handlePrivacyItemForCreated($model);
        }
    }

    /**
     * @throws ValidatorException
     */
    private function handleUpdatePrivacyStream(Model $model): bool
    {
        if (!$model instanceof Content) {
            return false;
        }

        $oldPrivacy = $model->privacy;

        if (!$model->wasRecentlyCreated) {
            $oldPrivacy = $model->getOriginal('privacy');
        }

        /*
         * In case custom privacy, options can be modified
         */
        if ($oldPrivacy == MetaFoxPrivacy::CUSTOM && $model->privacy == $oldPrivacy && empty($model->privacy_list)) {
            return false;
        }

        if ($oldPrivacy != MetaFoxPrivacy::CUSTOM && !$model->isDirty('privacy')) {
            return false;
        }

        // Delete old stream.
        $this->privacyStreamRepository()->deleteWhere([
            'item_id'   => $model->entityId(),
            'item_type' => $model->entityType(),
        ]);

        // Delete {item}_privacy_streams.
        if (method_exists($model, 'deletePrivacyStreams')) {
            $model->deletePrivacyStreams();
        }

        $privacyUidList = $this->privacyRepository()->getPrivacyIdsForContent($model);

        if (count($privacyUidList)) {
            $privacyStreams = array_map(function ($privacyId) use ($model) {
                return [
                    'privacy_id' => $privacyId,
                    'item_id'    => $model->entityId(),
                    'item_type'  => $model->entityType(),
                ];
            }, $privacyUidList);

            $this->privacyStreamRepository()->createMany($privacyStreams);

            if (method_exists($model, 'syncPrivacyStreams')) {
                $model->syncPrivacyStreams(array_map(function ($array) {
                    unset($array['item_type']);

                    return $array;
                }, $privacyStreams));
            }
        }

        return true;
    }
}
