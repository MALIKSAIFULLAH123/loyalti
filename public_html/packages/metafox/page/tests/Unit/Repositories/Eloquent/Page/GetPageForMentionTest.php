<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\Page;

use Illuminate\Pagination\Paginator;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use Tests\TestCase;

class GetPageForMentionTest extends TestCase
{
    /**
     * @return PageRepositoryInterface
     */
    public function testInstance()
    {
        $repository = resolve(PageRepositoryInterface::class);
        $this->assertInstanceOf(PageRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     * @return array<int, mixed>
     */
    public function testSuccess(PageRepositoryInterface $repository): array
    {
        $this->markTestIncomplete();
        $user = $this->createNormalUser();

        $this->actingAs($user);

        Page::factory()
            ->setUser($user)
            ->create();

        $params = [
            'q'     => '',
            'limit' => 20,
        ];

        /** @var Paginator $results */
        $results = $repository->getPageForMention($user, $params);

        $this->expectNotToPerformAssertions();

        return [$user, $repository];
    }

    /**
     * @depends testSuccess
     *
     * @param array<int, mixed> $data
     */
    public function testSuccessWithSearch(array $data)
    {
        $this->markTestIncomplete();
        /**
         * @var User                    $user
         * @var PageRepositoryInterface $repository
         */
        [$user, $repository] = $data;

        $name = 'venomTrMF';

        $page1 = Page::factory()->setUser($user)
            ->create(['name' => $name]);

        $page2 = Page::factory()->setUser($user)
            ->create();

        $params = [
            'q'     => 'venomTr',
            'limit' => 20,
        ];

        $results = $repository->getPageForMention($user, $params);
        $this->assertTrue($results->isNotEmpty());
        $resultsConverted = $this->convertForTest($results->items());
        $this->assertArrayHasKey($page1->entityId(), $resultsConverted);
        $this->assertArrayNotHasKey($page2->entityId(), $resultsConverted);
    }
}
