<?php

namespace MetaFox\Page\Tests\Unit\Http\Resources\v1\PageInvite;

use MetaFox\Page\Http\Resources\v1\PageInvite\PageInviteEmbed as Resource;
use MetaFox\Page\Http\Resources\v1\PageInvite\PageInviteEmbedCollection as ResourceCollection;
use MetaFox\Page\Models\PageInvite as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
| @link MetaFox\Page\Http\Resources\v1\PageInvite\PageInviteEmbed
*/

class PageInviteEmbedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestIncomplete('coming soon!');
    }

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
