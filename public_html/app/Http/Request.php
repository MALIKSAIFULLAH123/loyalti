<?php

namespace App\Http;

use Illuminate\Http\Request as LaravelRequest;

class Request extends LaravelRequest
{
    protected function prepareRequestUri()
    {
        // fix apache rewrite mode set redirect url instead of REQUEST_URI when
        if ((int) $this->server->get('REDIRECT_STATUS', '200') && $this->server->has('REDIRECT_URL')) {
            $requestUri = $this->server->get('REDIRECT_URL');
            $requestUri =  trim($requestUri, '/');
            $this->server->set('REQUEST_URI', $requestUri === '' ? '/' : $requestUri);
            return $requestUri;
        }

        return parent::prepareRequestUri();
    }
}