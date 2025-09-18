<?php

namespace MetaFox\Video\Tests\Unit\Http\Resources\v1\Video;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Http\Resources\v1\Video\VideoItem as Resource;
use MetaFox\Video\Http\Resources\v1\Video\VideoItemCollection as ResourceCollection;
use MetaFox\Video\Models\Video as Model;
use Tests\TestCase;

class VideoItemTest extends TestCase
{
    /**
     * @return array<int, mixed> $model
     */
    public function testCreate(): array
    {

        /** @var Model $model */
        $model = Model::factory()->create();

        $model->refresh();

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertNotEmpty($model->id);
        $this->assertInstanceOf(User::class, $user);

        return [$model, $user];
    }

    /**
     * @depends testCreate
     *
     * @param  array<int, mixed> $params
     * @return array<int,        mixed>
     */
    public function testResource(array $params): array
    {
        [$model, $user] = $params;
        $this->be($user);
        $resource = new Resource($model);

        $res = $resource->toJson();
        $this->assertIsString($res);

        return $params;
    }

    /**
     * @depends testResource
     *
     * @param  array<int, mixed> $params
     * @return array<int,        mixed>
     */
    public function testCollection(array $params): array
    {
        [$model, $user] = $params;
        $this->be($user);
        $collection = new ResourceCollection([$model]);

        $res = $collection->toJson();
        $this->assertIsString($res);

        return $params;
    }
}
