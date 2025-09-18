<?php

namespace MetaFox\Page\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Platform\Support\Factory\HasSetState;

class PageInviteFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PageInvite::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status_id' => 0,
        ];
    }

    /**
     * @param  Page $page
     * @return self
     */
    public function setPage(Page $page): self
    {
        return $this->state(function () use ($page) {
            return [
                'page_id' => $page->entityId(),
            ];
        });
    }
}

// end
