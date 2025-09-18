<?php

namespace MetaFox\Activity\Tests\Unit\Http\Requests\v1\Feed;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use MetaFox\Activity\Http\Requests\v1\Feed\StoreRequest as Request;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\Eloquent\FeedRepository;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Poll;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------
 *  Http request test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Activity\Http\Controllers\Api\FeedController::$controllers;
 * stub: api_action_request_test.stub
 */

/**
 * Class StoreRequestTest.
 */
class CreateFeedTest extends TestCase
{
    public function buildForm($data)
    {
        $form = new Request($data);

        $form->setContainer(app())
            ->setRedirector(app(Redirector::class));

        return $form;
    }

    public function testUser(): User
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->assertInstanceOf(User::class, $user);

        return $user;
    }

    public function testFeedRepository(): FeedRepositoryInterface
    {
        $repository = resolve(FeedRepositoryInterface::class);

        $this->assertInstanceOf(FeedRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testUser
     * @throws AuthenticationException
     */
    public function testCreatePollSuccess(User $user)
    {
        $this->be($user);

        $form = $this->buildForm([
            'post_type'     => Poll::FEED_POST_TYPE,
            'privacy'       => 0,
            'poll_question' => $this->faker->text,
            'poll_answers'  => [
                ['answer' => $this->faker->sentence . rand(1, 999)],
                ['answer' => $this->faker->sentence . rand(1, 999)],
            ],
            'poll_caption' => $this->faker->text,
        ]);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }

    /**
     * @depends testUser
     * @depends testFeedRepository
     *
     * @param array<int, mixed> $params
     */
    public function testSharePollPostWithCloseTime(User $user, FeedRepositoryInterface $repository)
    {
        $this->actingAs($user);

        $closeTime = now()->addDays(10);

        $response = $repository->createFeed($user, $user, $user, [
            'user_status'   => $this->faker->text,
            'post_type'     => Poll::FEED_POST_TYPE,
            'privacy'       => MetaFoxPrivacy::EVERYONE,
            'poll_question' => $this->faker()->title,
            'poll_answers'  => [
                ['answer' => $this->faker()->sentence(), 'order' => 1],
                ['answer' => $this->faker()->sentence(), 'order' => 2],
                ['answer' => $this->faker()->sentence(), 'order' => 3],
            ],
            'poll_close_time' => $closeTime,
            'enable_close'    => 1,
            'poll_multiple'   => 1,
            'poll_public'     => 1,
        ]);

        $feedId = (int) Arr::get($response, 'id');

        $this->assertGreaterThanOrEqual(1, $feedId);

        $feed = $repository->find($feedId);

        $this->assertInstanceOf(Feed::class, $feed);

        $item = $feed->item;

        $this->assertInstanceOf(Poll::class, $item);

        $resultCloseTime = Carbon::parse($item->closed_at);

        $this->assertEquals($closeTime->timestamp, $resultCloseTime->timestamp);
    }
}
