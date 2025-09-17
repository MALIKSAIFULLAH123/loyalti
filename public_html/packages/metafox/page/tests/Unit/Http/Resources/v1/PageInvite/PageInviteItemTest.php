<?php

namespace MetaFox\Page\Tests\Unit\Http\Resources\v1\PageInvite;

use MetaFox\Page\Http\Resources\v1\PageInvite\PageInviteItem as Resource;
use MetaFox\Page\Http\Resources\v1\PageInvite\PageInviteItemCollection as ResourceCollection;
use MetaFox\Page\Models\PageInvite as Model;
use Tests\TestCase;

class PageInviteItemTest extends TestCase
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
