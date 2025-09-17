<?php

namespace Tests;

return function () {
    $state = State::factory();
    /*
    * @see \MetaFox\Blog\Http\Controllers\Api\v1\CategoryController::index
    */
    yield 'GET api/v1/blog-category' => [
        'url'        => $state->url('api/v1/blog-category'),
        'method'     => 'GET',
        'user'       => null,
        'data'       => [],
        'incomplete' => true,
        'status'     => 200,
    ];
};
