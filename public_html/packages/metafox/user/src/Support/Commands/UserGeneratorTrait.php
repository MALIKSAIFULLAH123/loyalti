<?php

namespace MetaFox\User\Support\Commands;

use MetaFox\User\Database\Factories\UserFactory;
use MetaFox\User\Database\Factories\UserProfileFactory;
use MetaFox\User\Models\User;

trait UserGeneratorTrait
{
    /**
     * @param array<mixed> $parameters
     *
     * @return User
     */
    public function createUser(array $parameters = []): User
    {
        return UserFactory::new($parameters)
            ->create()
            ->refresh();
    }
}
