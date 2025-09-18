<?php

namespace MetaFox\Video\Tests\Unit\Listeners;

use Exception;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\TempFileModel;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Listeners\MediaUploadListener;
use Tests\TestCase;

class MediaUploadListenerTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreateResource(): array
    {
        $listener = resolve(MediaUploadListener::class);

        $user       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $photoGroup = PhotoGroup::factory()->setUser($user)->setOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Content::class, $photoGroup);
        $this->assertNotEmpty($photoGroup->entityId());

        $tempFile = $this->createTempFile($user, 'test.mp4', 'video');
        $this->assertInstanceOf(TempFileModel::class, $tempFile);

        return [$listener, $user, $photoGroup, $tempFile];
    }

    /**
     * @depends testCreateResource
     *
     * @param array<int, mixed> $params
     *
     * @throws Exception
     */
    public function testHandle(array $params)
    {
        /**
         * @var MediaUploadListener $listener
         * @var User                $user
         * @var PhotoGroup          $photoGroup
         * @var TempFileModel       $tempFile
         */
        [$listener, $user, $photoGroup, $tempFile] = $params;

        $params = [
            'group_id' => $photoGroup->entityId(),
            'privacy'  => MetaFoxPrivacy::EVERYONE,
        ];
        $content = $listener->handle($user, $user, $tempFile->item_type, $tempFile, $params);
        $this->assertInstanceOf(Content::class, $content);
    }
}
