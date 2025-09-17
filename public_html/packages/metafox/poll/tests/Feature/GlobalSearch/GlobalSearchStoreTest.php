<?php

namespace MetaFox\Poll\Tests\Feature\GlobalSearch;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Poll;
use MetaFox\Search\Models\Search;
use Tests\TestCase;

class GlobalSearchStoreTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreate()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->be($user);
        $text = $this->faker->text;

        /** @var Poll $item */
        $item = Poll::factory()->setUserAndOwner($user)
            ->create([
                'privacy'     => MetaFoxPrivacy::EVERYONE,
                'text'        => $text,
                'text_parsed' => $text,
            ]);

        $search = Search::query()->where([
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
        ])->first();

        $this->assertInstanceOf(Search::class, $search);

        $this->assertSame($item->question, $search->title);
        $this->assertSame($text, $search->text);

        // Update.
        $newText = $this->faker->text;
        $newTitle = $this->faker->sentence;
        $item->fill([
            'question'    => $newTitle,
            'text'        => $newText,
            'text_parsed' => $newText,
        ])->save();

        $search = Search::query()->where([
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
        ])->first();

        $this->assertInstanceOf(Search::class, $search);

        $this->assertSame($newTitle, $search->title);
        $this->assertSame($newText, $search->text);
    }
}
