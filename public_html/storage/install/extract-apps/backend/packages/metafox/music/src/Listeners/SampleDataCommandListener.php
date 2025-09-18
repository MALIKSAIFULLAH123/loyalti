<?php

namespace MetaFox\Music\Listeners;

use MetaFox\Music\Database\Factories\AlbumFactory;
use MetaFox\Music\Database\Factories\PlaylistFactory;
use MetaFox\Music\Database\Factories\SongFactory as Factory;
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
     * @param  User      $user
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?User $admin, User $user): void
    {
        Factory::times(4)
            ->setUser($admin)
            ->setOwner($user)
            ->create([
                'privacy' => MetaFoxPrivacy::EVERYONE,
            ]);

        PlaylistFactory::times(2)
            ->setUser($admin)
            ->setOwner($user)
            ->create([
                'privacy' => MetaFoxPrivacy::EVERYONE,
            ]);

        PlaylistFactory::times(2)
            ->setUser($admin)
            ->setOwner($user)
            ->create([
                'privacy' => MetaFoxPrivacy::EVERYONE,
            ]);

        AlbumFactory::times(2)
            ->setUser($admin)
            ->setOwner($user)
            ->create([
                'privacy' => MetaFoxPrivacy::EVERYONE,
            ]);
    }
}
