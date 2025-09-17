<?php

namespace MetaFox\Socialite\Support;

use Illuminate\Support\Arr;
use SocialiteProviders\TikTok\Provider;

class Tiktok extends Provider
{
    /**
     * @inheritdoc
     */
    protected function getTokenFields($code)
    {
        $fields = [
            'client_key'    => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->redirectUrl,
        ];

        if ($this->request->has('code_verifier')) {
            Arr::set($fields, 'code_verifier', $this->request->get('code_verifier'));
        }

        return $fields;
    }
}
