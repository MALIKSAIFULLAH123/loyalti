<?php

namespace MetaFox\Page\Tests\Unit\Http\Resources\v1\PageCategory;

use MetaFox\Page\Http\Resources\v1\PageCategory\PageCategoryEmbed as Resource;
use MetaFox\Page\Http\Resources\v1\PageCategory\PageCategoryEmbedCollection as ResourceCollection;
use MetaFox\Page\Models\Category as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
| @link MetaFox\Page\Http\Resources\v1\Category\PageCategoryEmbed
*/

class PageCategoryEmbedTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate()
    {
        /** @var Model $model */
        $model = Model::factory()->create();

        $model->refresh();

        $this->assertNotEmpty($model->id);

        return $model;
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testResource(Model $model)
    {
        $resource = new Resource($model);

        $resource->toJson();

        // assert ...

        $this->markTestIncomplete('coming soon!');
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testCollection(Model $model)
    {
        $collection = new ResourceCollection([$model]);

        $collection->toJson();

        $this->markTestIncomplete('coming soon!');
    }
}
