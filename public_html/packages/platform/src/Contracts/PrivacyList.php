<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

interface PrivacyList extends Entity
{
    /**
     * @return array<mixed>
     */
    public function toPrivacyLists();
}
