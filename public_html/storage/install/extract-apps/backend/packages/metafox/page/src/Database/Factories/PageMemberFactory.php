<?php

namespace MetaFox\Page\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Page\Models\PageMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class PageMemberFactory.
 * @method PageMember create($attributes = [], ?Model $parent = null)
 */
class PageMemberFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PageMember::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_type' => PageMember::MEMBER,
            'user_id'     => $this->randomUserId(),
            'user_type'   => 'user',
        ];
    }

    /**
     * @param User $page
     *
     * @return self
     */
    public function setOwner(User $page)
    {
        return $this->state(function () use ($page) {
            return [
                'page_id' => $page->entityId(),
            ];
        });
    }

    /**
     * @return self
     */
    public function setAdmin()
    {
        return $this->state(function () {
            return [
                'member_type' => PageMember::ADMIN,
            ];
        });
    }
}

// end
