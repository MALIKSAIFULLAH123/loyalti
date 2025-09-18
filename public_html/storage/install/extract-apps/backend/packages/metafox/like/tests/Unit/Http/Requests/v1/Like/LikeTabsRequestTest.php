<?php

namespace MetaFox\Like\Tests\Unit\Http\Requests\v1\Like;

use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;
use MetaFox\Like\Http\Requests\v1\Like\LikeTabsRequest as Request;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class LikeTabsRequestTest.
 */
class LikeTabsRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('item_id', 'item_type'),
            $this->failIf('item_id', null, 'string'),
            $this->failIf('item_type', null, 0),
            $this->passIf([
                'item_id'   => 1,
                'item_type' => 'blog',
            ])
        );
    }
    public function buildForm($data): Request
    {
        $form = new Request($data);
        $form->setContainer(app())
            ->setRedirector(app(Redirector::class));

        return $form;
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

    /**
     * @depends testSuccess
     */
    public function testItemIdRequired(ContentModel $item)
    {
        $form = $this->buildForm([
            'item_id'   => null,
            'item_type' => $item->entityType(),
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    /**
     * @depends testSuccess
     */
    public function testItemIdNumeric(ContentModel $item)
    {
        $form = $this->buildForm([
            'item_id'   => 'Test',
            'item_type' => $item->entityType(),
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    /**
     * @depends testSuccess
     */
    public function testItemTypeRequired(ContentModel $item)
    {
        $form = $this->buildForm([
            'item_id'   => $item->entityId(),
            'item_type' => null,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    /**
     * @depends testSuccess
     */
    public function testItemTypeString(ContentModel $item)
    {
        $form = $this->buildForm([
            'item_id'   => $item->entityId(),
            'item_type' => 0,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
