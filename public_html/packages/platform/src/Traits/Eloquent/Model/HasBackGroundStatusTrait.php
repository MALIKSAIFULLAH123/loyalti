<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Trait HasBackGroundStatusTrait.
 */
trait HasBackGroundStatusTrait
{
    public function getBackgroundStatus(?int $bgStatusId = null): ?JsonResource
    {
        $bgStatusId = $this->processStatusBackgroundId($bgStatusId);

        if (!$bgStatusId) {
            return null;
        }

        $statusBackground = app('events')->dispatch('background-status.get_bg_status', [$bgStatusId], true);

        if (!$statusBackground instanceof Model) {
            return null;
        }

        return ResourceGate::asEmbed($statusBackground, null);
    }

    protected function processStatusBackgroundId(?int $bgStatusId = null): ?int
    {
        if ($bgStatusId) {
            return $bgStatusId;
        }

        if (!property_exists($this, 'attributes')) {
            return null;
        }

        return Arr::get($this->attributes, 'status_background_id');
    }
}
