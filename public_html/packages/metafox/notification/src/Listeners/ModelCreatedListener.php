<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Notification\Listeners;

use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Contracts\IsNotifyInterface;

/**
 * Class ModelCreatedListener.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class ModelCreatedListener
{
    /**
     * @param mixed $model
     */
    public function handle($model): void
    {
        if ($model instanceof IsNotifyInterface) {
            $response = $model->toNotification();

            if (is_array($response)) {
                Notification::send(...$response);
            }
        }
    }
}
