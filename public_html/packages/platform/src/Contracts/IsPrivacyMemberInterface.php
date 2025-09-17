<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

interface IsPrivacyMemberInterface
{
    public function userId();

    public function privacyId();
}
