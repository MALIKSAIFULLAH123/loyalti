<?php

namespace MetaFox\User\Password;

class v4Password
{
    /**
     * @param  string  $input
     * @param  string  $hash
     * @param  string  $salt
     * @return bool
     */
    public function check(string $input, string $hash, string $salt): bool
    {
        if (strlen($hash) > 32) {
            return password_verify($input, $hash);
        } else {
            return $hash === md5(md5($input).md5($salt));
        }
    }
}
