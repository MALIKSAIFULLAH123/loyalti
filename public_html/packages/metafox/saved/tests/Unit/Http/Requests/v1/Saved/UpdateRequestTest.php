<?php

namespace MetaFox\Saved\Tests\Unit\Http\Requests\v1\Saved;

use MetaFox\Platform\UserRole;
use MetaFox\Saved\Http\Requests\v1\Saved\UpdateRequest as Request;
use MetaFox\Saved\Models\SavedList;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([]),
            $this->failIf('is_unopened', null, 'string', [], 99),
            $this->failIf('saved_list_ids', 0, null, 'string'),
            $this->passIf('is_unopened', 1, 0),
        );
    }

    public function testSuccess()
    {
        $user      = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $savedList = SavedList::factory()->setUser($user)->create();
        $form      = $this->buildForm([
            'is_unopened'    => 1,
            'saved_list_ids' => [$savedList->entityId()],
        ]);
        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }
}
