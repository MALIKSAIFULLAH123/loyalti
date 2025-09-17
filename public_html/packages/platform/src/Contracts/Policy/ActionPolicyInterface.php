<?php

namespace MetaFox\Platform\Contracts\Policy;

/**
 * Interface ActionPolicyInterface
 * @package MetaFox\Platform\Contracts\Policy
 */
interface ActionPolicyInterface
{
    /**
     * @return string
     */
    public function getEntityType(): string;
}
