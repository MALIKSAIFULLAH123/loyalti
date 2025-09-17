<?php

namespace Tests;

return function () {
    $state = State::factory();
    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::index
    */
    yield 'GET api/v1/admincp/blog/category' => [
        'url'        => $state->url('api/v1/admincp/blog/category'),
        'method'     => 'GET',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::store
    */
    yield 'POST api/v1/admincp/blog/category' => [
        'url'        => $state->url('api/v1/admincp/blog/category'),
        'method'     => 'POST',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::toggleActive
    */
    yield 'PATCH api/v1/admincp/blog/category/active/{category}' => [
        'url'        => $state->url('api/v1/admincp/blog/category/active/{category}'),
        'method'     => 'PATCH',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::create
    */
    yield 'GET api/v1/admincp/blog/category/create' => [
        'url'        => $state->url('api/v1/admincp/blog/category/create'),
        'method'     => 'GET',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::default
    */
    yield 'POST api/v1/admincp/blog/category/default/{id}' => [
        'url'        => $state->url('api/v1/admincp/blog/category/default/{id}'),
        'method'     => 'POST',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::order
    */
    yield 'POST api/v1/admincp/blog/category/order' => [
        'url'        => $state->url('api/v1/admincp/blog/category/order'),
        'method'     => 'POST',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::show
    */
    yield 'GET api/v1/admincp/blog/category/{category}' => [
        'url'        => $state->url('api/v1/admincp/blog/category/{category}'),
        'method'     => 'GET',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::update
    */
    yield 'PUT api/v1/admincp/blog/category/{category}' => [
        'url'        => $state->url('api/v1/admincp/blog/category/{category}'),
        'method'     => 'PUT',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::destroy
    */
    yield 'DELETE api/v1/admincp/blog/category/{category}' => [
        'url'        => $state->url('api/v1/admincp/blog/category/{category}'),
        'method'     => 'DELETE',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::delete
    */
    yield 'GET api/v1/admincp/blog/category/{category}/delete' => [
        'url'        => $state->url('api/v1/admincp/blog/category/{category}/delete'),
        'method'     => 'GET',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryAdminController::edit
    */
    yield 'GET api/v1/admincp/blog/category/{category}/edit' => [
        'url'        => $state->url('api/v1/admincp/blog/category/{category}/edit'),
        'method'     => 'GET',
        'user'       => 'admin',
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];
};
