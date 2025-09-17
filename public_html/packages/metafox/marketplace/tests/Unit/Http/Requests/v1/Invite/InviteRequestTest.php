<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Requests\v1\Invite;

use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Marketplace\Http\Requests\v1\Invite\InviteRequest as Request;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Platform\UserRole;
use Tests\TestFormRequest;

class InviteRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('listing_id'),
            $this->failIf('listing_id', 0, 'string'),
            $this->failIf('user_ids', 0, 'string', ['string']),
            $this->passIf(['listing_id' => 1, 'user_ids' => [1]])
        );
    }

    public function testSuccess()
    {
        $listing = Listing::factory()->create();
        $user1   = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2   = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($user1)->setOwner($user2)->create();
        FriendFactory::new()->setUser($user2)->setOwner($user1)->create();

        $form = $this->buildForm([
            'listing_id' => $listing->id,
            'user_ids'   => [$user1->id, $user2->id],
        ]);
        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }
}
