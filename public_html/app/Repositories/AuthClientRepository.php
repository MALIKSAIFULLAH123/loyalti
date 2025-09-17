<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Cache;
use Laravel\Passport\ClientRepository;

class AuthClientRepository extends ClientRepository
{
    public function find($id)
    {
        return Cache::remember("auth_client($id)", 300, function () use ($id) {
            return parent::find($id);
        });
    }
}
