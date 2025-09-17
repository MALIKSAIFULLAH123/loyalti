<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Database\Factories\EventFactory as Factory;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Commands\UserGeneratorTrait;

/**
 * Class SampleDataCommandListener.
 * @ignore
 * @codeCoverageIgnore
 */
class SampleDataCommandListener
{
    use UserGeneratorTrait;

    /**
     * @param  User|null $admin
     * @param  User|null $user
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?User $admin, ?User $user): void
    {
        // Create 4 featured blogs for admin
        Factory::times(4)
            ->setUser($admin)
            ->setOwner($admin)
            ->create([
                'is_featured' => 1,
                'privacy'     => MetaFoxPrivacy::EVERYONE,
            ]);

        Factory::times(5)->setUser($user)->setOwner($user)->create();
    }
}
