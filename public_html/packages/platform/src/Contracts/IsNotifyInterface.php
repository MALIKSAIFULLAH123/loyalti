<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

interface IsNotifyInterface extends Entity
{
    /**
     * creator
     * result must contain
     * User owner.
     * notification class.
     *
     * @return array<mixed>|null
     */
    public function toNotification(): ?array;
}
