<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Resource;

use Illuminate\Support\Facades\Log;

/**
 * Class WebSetting.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren )
 */
class WebSetting extends Actions
{
    protected function initialize(): void
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $this->initialize();

        try {
            app('events')->dispatch('platform.resource_setting.override', [$this]);
        } catch (\Throwable $exception) {
            Log::error('override resource setting error: ' . $exception->getMessage());
            Log::error('override resource setting error trace: ' . $exception->getTraceAsString());
        }

        return parent::toArray();
    }
}
