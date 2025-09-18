<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\PageInvite;

use MetaFox\Page\Http\Requests\v1\PageInvite\StoreRequest;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\User;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Page\Http\Controllers\Api\v1\PageInviteController::store()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return StoreRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('page_id', 'user_ids'),
            $this->failIf('page_id', 0, null, [], 'string'),
            $this->failIf('user_ids', 0, 'string', null)
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $user = $this->createNormalUser();
        $this->be($user);
        $user1 = $this->createNormalUser();

        $page = Page::factory()->setUser($user)->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(User::class, $user1);
        $this->assertInstanceOf(Page::class, $page);

        return [$user, $user1, $page];
    }

    /**
     * @depends testInstance
     */
    public function testRequestSuccess(array $params)
    {
        [$user, $user1, $page] = $params;

        $this->actingAs($user);

        $form = $this->buildForm([
            'page_id'  => $page->entityId(),
            'user_ids' => [
                $user1->entityId(),
            ],
        ]);

        $form->validateResolved();
        $data = $form->validated();
        $this->assertNotEmpty($data);
        $this->assertNotEmpty($data['user_ids']);
    }

    protected function beforeTest()
    {
        $user = $this->createNormalUser();
        $this->be($user);
    }
}
