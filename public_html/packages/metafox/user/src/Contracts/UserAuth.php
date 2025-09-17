<?php

namespace MetaFox\User\Contracts;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Interface UserAuth.
 */
interface UserAuth
{
    /**
     * authenticate login request.
     *
     * @param  FormRequest $request
     * @return mixed
     */
    public function login(FormRequest $request);

    /**
     * authorize login request.
     *
     * @param  FormRequest $request
     * @return mixed
     */
    public function authorize(FormRequest $request);
}
