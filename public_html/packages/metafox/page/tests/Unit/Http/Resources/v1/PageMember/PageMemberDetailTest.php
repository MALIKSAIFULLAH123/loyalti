<?php

namespace MetaFox\Page\Tests\Unit\Http\Resources\v1\PageMember;

use MetaFox\Page\Http\Resources\v1\PageMember\PageMemberDetail as Resource;
use MetaFox\Page\Models\PageMember as Model;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
|
| @link \MetaFox\Page\Http\Resources\v1\PageMember\PageMemberDetail
*/

class PageMemberDetailTest extends TestCase
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
}
