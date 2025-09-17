<?php

namespace MetaFox\Socialite\Support;

use Laravel\Socialite\Two\FacebookProvider;
use Laravel\Socialite\Two\User;
use SocialiteProviders\Manager\ConfigTrait;

class Facebook extends FacebookProvider
{
    use ConfigTrait;

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject($user)
    {
        $avatarUrl = $this->graphUrl . '/' . $this->version . '/' . $user['id'] . '/picture';

        $avatarOriginalUrl = $avatarUrl . '?width=1920';

        return (new User)->setRaw($user)->map([
            'id'              => $user['id'],
            'nickname'        => null,
            'name'            => $user['name'] ?? null,
            'email'           => $user['email'] ?? null,
            'avatar'          => $avatarUrl,
            'avatar_original' => $avatarOriginalUrl,
            'profileUrl'      => $user['link'] ?? null,
        ]);
    }
}
