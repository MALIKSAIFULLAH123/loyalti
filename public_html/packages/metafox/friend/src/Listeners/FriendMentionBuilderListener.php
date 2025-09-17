<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MetaFox\Friend\Support\Browse\Scopes\Friend\ViewFriendsScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as UserModel;

class FriendMentionBuilderListener
{
    public function handle(?User $context, User $user, array $attributes): Builder
    {
        // Check friend scope
        $viewFriendsScope = new ViewFriendsScope();

        $viewFriendsScope->setUserId($user->entityId())
            ->setIsMention(Arr::get($attributes, 'is_mention', true))
            ->setTable('user_entities');

        $builder = DB::table('user_entities');

        $builder->addScope($viewFriendsScope)
            ->select('user_entities.id')
            ->where('user_entities.entity_type', '=', UserModel::ENTITY_TYPE);

        return $builder;
    }
}
