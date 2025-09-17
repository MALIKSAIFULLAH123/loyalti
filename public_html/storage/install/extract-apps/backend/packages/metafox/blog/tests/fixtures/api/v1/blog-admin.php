<?php

namespace Tests;

return function () {
    $state = State::factory();
    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogAdminController::index
    */
    yield 'GET api/v1/admincp/blog' => [
        'url'        => $state->url('api/v1/admincp/blog'),
        'method'     => 'GET',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogAdminController::store
    */
    yield 'POST api/v1/admincp/blog' => [
        'url'        => $state->url('api/v1/admincp/blog'),
        'method'     => 'POST',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogAdminController::show
    */
    yield 'GET api/v1/admincp/blog/{blog}' => [
        'url'        => $state->url('api/v1/admincp/blog/{blog}'),
        'method'     => 'GET',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogAdminController::update
    */
    yield 'PUT api/v1/admincp/blog/{blog}' => [
        'url'        => $state->url('api/v1/admincp/blog/{blog}'),
        'method'     => 'PUT',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogAdminController::destroy
    */
    yield 'DELETE api/v1/admincp/blog/{blog}' => [
        'url'        => $state->url('api/v1/admincp/blog/{blog}'),
        'method'     => 'DELETE',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];
};
