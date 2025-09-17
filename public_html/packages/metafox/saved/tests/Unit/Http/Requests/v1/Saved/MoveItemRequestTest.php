<?php

namespace MetaFox\Saved\Tests\Unit\Http\Requests\v1\Saved;

use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Http\Requests\v1\Saved\MoveItemRequest as Request;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedList;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Saved\Http\Controllers\Api\v1\SavedController::moveItem()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class MoveItemRequestTest.
 */
class MoveItemRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('item_id'),
            $this->failIf('item_id', 0, null, [], 'string'),
            $this->failIf('collection_ids', 0, null, 'string', [0]),
        );
    }

    public function testSuccess()
    {
        $user  = $this->createNormalUser();
        $this->actingAs($user);

        $list  = SavedList::factory()->setUser($user)->create();
        $item  = $this->contentFactory()->setUser($user)->setOwner($user)->create();
        $saved = Saved::factory()->setItem($item)->setUser($user)->create();

        $form = $this->buildForm([
            'item_id'       => $saved->entityId(),
            'collection_id' => $list->entityId(),
        ]);
        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }
}
