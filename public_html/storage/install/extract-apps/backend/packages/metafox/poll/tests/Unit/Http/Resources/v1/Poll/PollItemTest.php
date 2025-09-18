<?php

namespace MetaFox\Poll\Tests\Unit\Http\Resources\v1\Poll;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Http\Resources\v1\Poll\PollItem as Resource;
use MetaFox\Poll\Http\Resources\v1\Poll\PollItemCollection as ResourceCollection;
use MetaFox\Poll\Models\Poll as Model;
use Tests\TestCase;

class PollItemTest extends TestCase
{
    /**
     * @return Model $model
     */
    public function testCreate(): Model
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
     * @param  Model $model
     * @return User
     */
    public function testResource(Model $model): User
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        $resource = new Resource($model);

        $data = $resource->toJson();

        $this->assertIsString($data);

        return $user;
    }

    /**
     * @depends testCreate
     * @depends testResource
     *
     * @param Model $model
     * @param User  $user
     */
    public function testCollection(Model $model, User $user)
    {
        $this->actingAs($user);
        $collection = new ResourceCollection([$model]);

        $data = $collection->toJson();

        $this->assertIsString($data);
    }

    /**
     * @depends testCreate
     * @depends testResource
     *
     * @param Model $model
     * @param User  $user
     */
    public function testPollShouldNotShareableWhenHasOnlyMePrivacy(Model $model, User $user)
    {
        $this->actingAs($user);

        $model->privacy = MetaFoxPrivacy::ONLY_ME;
        $model->save();

        $resource = new Resource($model);
        $data     = $resource->toArray(null);

        $this->assertIsArray($data);
    }

    /**
     * @depends testCreate
     * @depends testResource
     *
     * @param Model $model
     * @param User  $user
     */
    public function testPollShouldShareableWhenHasCustomPrivacy(Model $model, User $user)
    {
        $this->actingAs($user);

        $model->privacy = MetaFoxPrivacy::CUSTOM;
        $model->save();

        $resource = new Resource($model);
        $data     = $resource->toArray(null);

        $this->assertIsArray($data);
    }

    /**
     * @depends testCreate
     * @depends testResource
     *
     * @param Model $model
     * @param User  $user
     */
    public function testPollShouldShareableWhenHasFriendsOfFriendsPrivacy(Model $model, User $user)
    {
        $this->actingAs($user);

        $model->privacy = MetaFoxPrivacy::FRIENDS_OF_FRIENDS;
        $model->save();

        $resource = new Resource($model);
        $data     = $resource->toArray(null);

        $this->assertIsArray($data);
    }

    /**
     * @depends testCreate
     * @depends testResource
     *
     * @param Model $model
     * @param User  $user
     */
    public function testPollShouldShareableWhenHasFriendsPrivacy(Model $model, User $user)
    {
        $this->actingAs($user);

        $model->privacy = MetaFoxPrivacy::FRIENDS;
        $model->save();

        $resource = new Resource($model);
        $data     = $resource->toArray(null);

        $this->assertIsArray($data);
    }

    /**
     * @depends testCreate
     * @depends testResource
     *
     * @param Model $model
     * @param User  $user
     */
    public function testPollShouldShareableWhenHasPublicPrivacy(Model $model, User $user)
    {
        $this->actingAs($user);

        $model->privacy = MetaFoxPrivacy::EVERYONE;
        $model->save();

        $resource = new Resource($model);
        $data     = $resource->toArray(null);

        $this->assertIsArray($data);
    }
}
