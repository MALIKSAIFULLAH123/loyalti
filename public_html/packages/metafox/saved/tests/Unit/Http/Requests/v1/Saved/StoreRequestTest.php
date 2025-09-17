<?php

namespace MetaFox\Saved\Tests\Unit\Http\Requests\v1\Saved;

use MetaFox\Platform\UserRole;
use MetaFox\Saved\Http\Requests\v1\Saved\StoreRequest as Request;
use MetaFox\Saved\Models\SavedList;
use Tests\TestCase;
use Tests\TestFormRequest;

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
            $this->shouldRequire('item_id', 'item_type'),
            $this->passIf('item_id', 0, 1),
            $this->failIf('item_id', [], 'string', null),
            $this->failIf('item_type', 0, [], null),
            $this->failIf('saved_list_ids', 0, [0], null),
        );
    }

    public function testSuccess()
    {
        $user      = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $savedList = SavedList::factory()->setUser($user)->create();
        $form      = $this->buildForm([
            'item_id'        => 1,
            'item_type'      => 'test',
            'saved_list_ids' => [$savedList->entityId()],
        ]);
        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }
}
