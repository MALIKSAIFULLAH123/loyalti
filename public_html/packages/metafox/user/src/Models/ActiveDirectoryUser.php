<?php

namespace MetaFox\User\Models;

use Illuminate\Support\Facades\Auth;
use LdapRecord\Models\OpenLDAP\User;

class ActiveDirectoryUser extends User
{
    /**
     * @param  string                        $username
     * @return \LdapRecord\Models\Model|null
     */
    public function findForPassport($username)
    {
        return $this->where('uid', $username)->first();
    }

    /**
     * @param  string                                                $username
     * @param  string                                                $password
     * @return false|\Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function findAndValidateForPassport($username, $password)
    {
        if (Auth::guard('web')->attempt(['uid' => $username, 'password' => $password])) {
            return Auth::guard('web')->user();
        }

        return false;
    }
}
