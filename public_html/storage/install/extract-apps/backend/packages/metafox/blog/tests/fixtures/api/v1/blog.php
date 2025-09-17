<?php

namespace Tests;

return function () {
    $state = State::factory();
    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::index
    */
    yield 'GET api/v1/blog' => [
        'url'        => $state->url('api/v1/blog'),
        'method'     => 'GET',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::store
    */
    yield 'POST api/v1/blog' => [
        'url'        => $state->url('api/v1/blog'),
        'method'     => 'POST',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::approve
    */
    yield 'PATCH api/v1/blog/approve/{id}' => [
        'url'        => $state->url('api/v1/blog/approve/{id}'),
        'method'     => 'PATCH',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::feature
    */
    yield 'PATCH api/v1/blog/feature/{id}' => [
        'url'        => $state->url('api/v1/blog/feature/{id}'),
        'method'     => 'PATCH',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::formStore
    */
    yield 'GET api/v1/blog/form' => [
        'url'        => $state->url('api/v1/blog/form'),
        'method'     => 'GET',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::formUpdate
    */
    yield 'GET api/v1/blog/form/{id}' => [
        'url'        => $state->url('api/v1/blog/form/{id}'),
        'method'     => 'GET',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::publish
    */
    yield 'PATCH api/v1/blog/publish/{id}' => [
        'url'        => $state->url('api/v1/blog/publish/{id}'),
        'method'     => 'PATCH',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::searchForm
    */
    yield 'GET api/v1/blog/search-form' => [
        'url'        => $state->url('api/v1/blog/search-form'),
        'method'     => 'GET',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::sponsorInFeed
    */
    yield 'PATCH api/v1/blog/sponsor-in-feed/{id}' => [
        'url'        => $state->url('api/v1/blog/sponsor-in-feed/{id}'),
        'method'     => 'PATCH',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::sponsor
    */
    yield 'PATCH api/v1/blog/sponsor/{id}' => [
        'url'        => $state->url('api/v1/blog/sponsor/{id}'),
        'method'     => 'PATCH',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::show
    */
    yield 'GET api/v1/blog/{blog}' => [
        'url'        => $state->url('api/v1/blog/{blog}'),
        'method'     => 'GET',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::update
    */
    yield 'PUT api/v1/blog/{blog}' => [
        'url'        => $state->url('api/v1/blog/{blog}'),
        'method'     => 'PUT',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];

    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\BlogController::destroy
    */
    yield 'DELETE api/v1/blog/{blog}' => [
        'url'        => $state->url('api/v1/blog/{id}'),
        'method'     => 'DELETE',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];
};
