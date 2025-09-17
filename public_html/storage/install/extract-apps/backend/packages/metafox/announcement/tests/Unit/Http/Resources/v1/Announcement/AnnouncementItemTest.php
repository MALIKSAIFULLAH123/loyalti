<?php

namespace MetaFox\Announcement\Tests\Unit\Http\Resources\v1\Announcement;

use MetaFox\Announcement\Http\Resources\v1\Announcement\AnnouncementItem as Resource;
use MetaFox\Announcement\Http\Resources\v1\Announcement\AnnouncementItemCollection as ResourceCollection;
use MetaFox\Announcement\Models\Announcement as Model;
use MetaFox\Announcement\Models\Style;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class AnnouncementItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
    {
        $user = $this->createUser()->assignRole(UserRole::ADMIN_USER);

        $style = Style::query()->first();
        $this->assertInstanceOf(Style::class, $style);

        /** @var Model $model */
        $model = Model::factory()->setUser($user)->setStyle($style)->create();

        $model->refresh();

        $this->assertNotEmpty($model->entityId());

        return $model;
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testResource(Model $model)
    {
        $this->be($model->user);

        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);
    }

    /**
     * @depends testCreate
     *
     * @param Model $model
     */
    public function testCollection(Model $model)
    {
        $this->be($model->user);

        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }
}
