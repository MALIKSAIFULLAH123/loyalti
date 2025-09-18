<?php

namespace MetaFox\Page\Tests\Unit\Http\Resources\v1\Page;

use MetaFox\Page\Http\Resources\v1\Page\PageDetail as Resource;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Resource Detail Test
|--------------------------------------------------------------------------
|
| @link \MetaFox\Page\Http\Resources\v1\Page\PageDetail
*/

class PageDetailTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreate(): array
    {
        $user     = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $category = Category::factory()->create();

        $this->actingAs($user);

        $model = Model::factory()->setUser($user)->create([
            'category_id' => $category->entityId(),
        ]);

        $model->refresh();

        $this->assertNotEmpty($model->entityId());

        return [$model, $user];
    }

    /**
     * @depends testCreate
     *
     * @param array<int, mixed> $data
     */
    public function testResource(array $data)
    {
        [$model, $user] = $data;
        $this->be($user);

        $resource = new Resource($model);

        $resource->toJson();

        // assert ...

        $this->markTestIncomplete('coming soon!');
    }
}
