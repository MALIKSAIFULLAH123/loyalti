<?php

namespace MetaFox\Blog\Tests\Api\v1;

use Tests\TestCases\TestApiFixture;

class BlogApiTest extends TestApiFixture
{
    /**
     * @link \Tests\TestCases\TestApiFixture::testRequest
     */
    public static function provideFixtures()
    {
        /*
        * Directory packages/metafox/blog/tests/fixtures/api/v1/
        */
        return static::loadFixtures([
            // 'api/v1/blog-admin.php',
            // 'api/v1/category-admin.php',
             'api/v1/blog.php',
            // 'api/v1/category.php',
        ]);
    }
}
