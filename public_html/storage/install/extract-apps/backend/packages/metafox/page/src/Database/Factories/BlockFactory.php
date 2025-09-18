<?php

namespace MetaFox\Page\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MetaFox\Page\Models\Block;
use MetaFox\Page\Models\PageMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

class BlockFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Block::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_type' => PageMember::MEMBER,
        ];
    }

    /**
     * @param  User  $page
     * @return $this
     */
    public function setOwner(User $page): self
    {
        return $this->state(function () use ($page) {
            return [
                'page_id' => $page->entityId(),
            ];
        });
    }

    /**
     * @return $this
     */
    public function setAdmin(): self
    {
        return $this->state(function () {
            return [
                'member_type' => PageMember::ADMIN,
            ];
        });
    }
}
