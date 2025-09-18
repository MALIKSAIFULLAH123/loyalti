<?php

namespace MetaFox\Socialite\Support;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User;
use SocialiteProviders\Manager\ConfigTrait;

class Google extends GoogleProvider
{
    use ConfigTrait;

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject($user)
    {
        $user['id']             = Arr::get($user, 'sub');
        $user['verified_email'] = Arr::get($user, 'email_verified');
        $user['link']           = Arr::get($user, 'profile');
        $avatarUrl              = Arr::get($user, 'picture');
        $avatarUrl              = str_replace('=s96-c', '=s512-c', $avatarUrl);

        return (new User)->setRaw($user)->map([
            'id'              => Arr::get($user, 'sub'),
            'nickname'        => Arr::get($user, 'nickname'),
            'name'            => Arr::get($user, 'name'),
            'email'           => Arr::get($user, 'email'),
            'avatar'          => $avatarUrl,
            'avatar_original' => $avatarUrl,
        ]);
    }
}
