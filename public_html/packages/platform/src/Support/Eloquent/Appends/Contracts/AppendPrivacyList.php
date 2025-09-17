<?php

namespace MetaFox\Platform\Support\Eloquent\Appends\Contracts;

/**
 * Interface AppendPrivacyList
 * @package MetaFox\Platform\Support\Eloquent\Appends\Contracts
 */
interface AppendPrivacyList
{
    /**
     * Laravel syntax initialize + ClassName will auto execute.
     */
//    public function initializeAppendPrivacyListTrait();

    public function setPrivacyListAttribute($privacyList = []);

    public function getPrivacyListAttribute();
}
