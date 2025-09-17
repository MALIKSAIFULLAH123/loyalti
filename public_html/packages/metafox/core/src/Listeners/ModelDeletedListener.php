<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Models\Link;
use MetaFox\Core\Traits\IsPrivacyItemTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;
use MetaFox\Platform\Contracts\PrivacyList;
use MetaFox\Platform\Contracts\User;

class ModelDeletedListener
{
    use IsPrivacyItemTrait;

    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        if ($model instanceof PrivacyList) {
            $this->privacyRepository()->deleteWhere([
                'item_id'   => $model->entityId(),
                'item_type' => $model->entityType(),
            ]);
        }

        if ($model instanceof User) {
            $this->privacyRepository()->deleteWhere([
                'owner_id'   => $model->entityId(),
                'owner_type' => $model->entityType(),
            ]);
            $this->privacyRepository()->deleteWhere([
                'item_id'   => $model->entityId(),
                'item_type' => $model->entityType(),
            ]);
        }

        if ($model instanceof IsPrivacyItemInterface) {
            $this->handlePrivacyItemForDeleted($model);
        }

        if ($model instanceof Content) {
            // Delete {item}_privacy_streams.
            if (method_exists($model, 'deletePrivacyStreams')) {
                $model->deletePrivacyStreams();
            }

            if ($model instanceof Link) {
                app('events')->dispatch('notification.delete_mass_notification_by_item', [$model], true);
            }
        }
    }
}
