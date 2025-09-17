<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform;

/**
 * Class UserPrivacy.
 *
 * @see     MetaFoxPrivacy
 */
class UserPrivacy
{
    /**
     * @return array<int, string>
     */
    public static function getPrivacy(): array
    {
        return [
            MetaFoxPrivacy::EVERYONE => 'phrase.user_privacy.anyone',
            MetaFoxPrivacy::FRIENDS  => 'phrase.user_privacy.friends_only',
            MetaFoxPrivacy::ONLY_ME  => 'phrase.user_privacy.no_one',
        ];
    }
}
