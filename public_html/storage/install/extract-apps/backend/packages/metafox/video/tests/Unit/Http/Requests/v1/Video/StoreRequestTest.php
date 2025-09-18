<?php

namespace MetaFox\Video\Tests\Unit\Http\Requests\v1\Video;

use Illuminate\Http\Testing\File;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Video\Http\Requests\v1\Video\StoreRequest as Request;
use MetaFox\Video\Models\Video;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Video\Http\Controllers\Api\VideoController::$controllers;
 * stub: api_action_request_test.stub
 */

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('title', 'privacy'),
            $this->failIf('text', 0, []),
            $this->failIf('video_url', 0),
            $this->failIf('owner_id', ),
            $this->withSampleParameters('privacy', 'categories', 'title'),
        );
    }

    /**
     * @return array<mixed>
     */
    public function testCreateResources(): array
    {
        $this->markTestIncomplete('coming soon!');

        $user = $this->createNormalUser();
        $this->be($user);
        $this->assertInstanceOf(User::class, $user);

        $video = Video::factory()->setUser($user)->create();

        return [$user, $video];
    }

    /**
     * @depends testCreateResources
     *
     * @params  array<mixed> $resources
     */
    public function testRequestShouldValidWhenTextValueIsString($resources)
    {
        $form = $this->buildForm([
            'title'   => $this->faker->words(5, true),
            'file'    => File::create('video.mp4', 1),
            'privacy' => MetaFoxPrivacy::EVERYONE,
            'text'    => $this->faker->text,
        ]);

        $form->validateResolved();
        $data = $form->validated();
        $this->assertNotEmpty($data);
        $this->assertIsString($data['text']);
    }

    /**
     * @depends testCreateResources
     *
     * @params  array<mixed> $resources
     */
    public function testRequestShouldValidWhenTextValueIsEmptyString($resources)
    {
        [, $tempFile] = $resources;
        $form         = $this->buildForm([
            'title'   => $this->faker->words(3, true),
            'file'    => [],
            'privacy' => MetaFoxPrivacy::EVERYONE,
            'text'    => '',
        ]);

        $form->validateResolved();
        $data = $form->validated();
        $this->assertNotEmpty($data);
        $this->assertIsString($data['text']);
    }

    /**
     * @depends testCreateResources
     *
     * @params  array<mixed> $resources
     */
    public function testRequestShouldValidWhenTextValueIsNull($resources)
    {
        [, $tempFile] = $resources;
        $form         = $this->buildForm([
            'title' => $this->faker->words(4, true),
            'file'  => [
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
            'text'    => null,
        ]);

        $form->validateResolved();
        $data = $form->validated();
        $this->assertNotEmpty($data);
        $this->assertEmpty($data['text']);
    }

    /**
     * @depends testCreateResources
     *
     * @params  array<mixed> $resources
     */
    public function testRequestShouldThrowExceptionWhenTextValueIsNumeric($resources)
    {
        [, $tempFile] = $resources;
        $form         = $this->buildForm([
            'title' => $this->faker->words(6, true),
            'file'  => [
                'temp_file' => $tempFile->entityId(),
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
            'text'    => -1,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
