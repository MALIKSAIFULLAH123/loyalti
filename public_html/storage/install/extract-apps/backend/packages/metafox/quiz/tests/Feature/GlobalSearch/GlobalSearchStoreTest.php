<?php

namespace MetaFox\Quiz\Tests\Feature\GlobalSearch;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz;
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

        /** @var Quiz $item */
        $item = Quiz::factory()->setUserAndOwner($user)
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

        $this->assertSame($item->title, $search->title);
        $this->assertSame($text, $search->text);

        // Update.
        $newText = $this->faker->text;
        $newTitle = $this->faker->sentence;
        $item->fill([
            'title'       => $newTitle,
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
