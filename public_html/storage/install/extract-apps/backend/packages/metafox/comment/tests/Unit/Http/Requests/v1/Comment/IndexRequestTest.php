<?php

namespace MetaFox\Comment\Tests\Unit\Http\Requests\v1\Comment;

use MetaFox\Comment\Http\Requests\v1\Comment\IndexRequest as Request;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('item_id', 'item_type'),
            $this->withSampleParameters('page', 'limit', 'sort', 'sort_type', ),
            $this->failIf('item_id', null, [], 'string'),
            $this->failIf('item_type', 0, null, []),
            $this->failIf('parent_id', 0, null, [], 'string'),
            $this->failIf('last_id', 0, null, [], 'string'),
            $this->failIf('excludes', 0, 'string'),
        );
    }

    public function testSuccess(): ContentModel
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = ContentModel::factory()->setUser($user)->setOwner($user)->create();

        $form = $this->buildForm([
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
        ]);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());

        return $item;
    }
}
