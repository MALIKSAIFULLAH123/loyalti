<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

/**
 * Interface BigNumberId.
 */
interface BigNumberId extends Entity
{
    public function setEntityId(int $id): void;
}
