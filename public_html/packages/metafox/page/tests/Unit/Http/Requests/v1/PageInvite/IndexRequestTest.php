<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\PageInvite;

use MetaFox\Page\Http\Requests\v1\PageInvite\IndexRequest as Request;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Page\Http\Controllers\Api\v1\PageInviteController::index()
 * stub: /packages/requests/api_action_request_test.stub
 */

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
            $this->shouldRequire('page_id'),
            $this->failIf('page_id', 0, [], 'string'),
            $this->withSampleParameters('q', 'limit', 'page')
        );
    }

    public function testSuccess(): Page
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $page = Page::factory()->setUser($user)->create();

        $form = $this->buildForm([
            'page_id' => $page->entityId(),
        ]);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());

        return $page;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->makeOne();
        $this->actingAs($this->user);
    }
}
