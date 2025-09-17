<?php

namespace MetaFox\Blog\Tests\Feature\GlobalSearch;

use Illuminate\Support\Carbon;
use MetaFox\Blog\Models\Blog;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Search\Models\Search;
use MetaFox\Search\Repositories\SearchRepositoryInterface;
use Tests\TestCase;

class GlobalSearchStorePublicTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function testCreate(): array
    {
        $service = resolve(SearchRepositoryInterface::class);
        $this->assertInstanceOf(SearchRepositoryInterface::class, $service);

        $context = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->be($context);

        $friend   = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $stranger = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($context)->setOwner($friend)->create();
        FriendFactory::new()->setUser($friend)->setOwner($context)->create();

        $blockedUser = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->blockUser($context, $blockedUser);

        $text  = $this->faker->text;
        $title = $this->faker->sentence;

        $item = Blog::factory()
            ->setUser($context)
            ->setOwner($context)
            ->create([
                'privacy'     => MetaFoxPrivacy::EVERYONE,
                'title'       => $title,
                'text'        => $text,
                'text_parsed' => $text,
            ]);

        $search = Search::query()->where([
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
        ])->first();

        $this->assertInstanceOf(Search::class, $search);

        $this->assertSame($item->title, $search->title);
        $this->assertSame($text, $search->text);

        // Update.
        $newText     = $this->faker->text;
        $matchSearch = Carbon::now()->timestamp;
        $newTitle    = $this->faker->sentence . ' ' . $matchSearch;
        $item->fill([
            'title'       => $newTitle,
            'text'        => $newText,
            'text_parsed' => $newText,
        ])->save();

        $searchData = Search::query()->where([
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
        ])->get();

        $this->assertSame(1, $searchData->count());

        $search = $searchData->first();

        $this->assertInstanceOf(Search::class, $search);

        $this->assertSame($newTitle, $search->title);
        $this->assertSame($newText, $search->text);

        return [$service, $context, $friend, $stranger, $blockedUser, $matchSearch];
    }

    /**
     * @depends testCreate
     *
     * @param array<mixed> $params
     */
    public function testSearchPrivacy(array $params)
    {
        /**
         * @var SearchRepositoryInterface $service
         * @var User                      $context
         * @var User                      $friend
         * @var User                      $stranger
         * @var User                      $blockedUser
         * @var string                    $matchSearch
         */
        [$service, $context, $friend, $stranger, $blockedUser, $matchSearch] = $params;

        $this->assertSame(1, $service->searchItems($context, ['q' => $matchSearch])->count());
        $this->assertSame(1, $service->searchItems($friend, ['q' => $matchSearch])->count());
        $this->assertSame(1, $service->searchItems($stranger, ['q' => $matchSearch])->count());
        $this->assertSame(0, $service->searchItems($blockedUser, ['q' => $matchSearch])->count());
    }
}
