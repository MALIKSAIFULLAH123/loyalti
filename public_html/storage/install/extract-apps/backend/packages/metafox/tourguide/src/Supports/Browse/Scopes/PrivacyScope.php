<?php

namespace MetaFox\TourGuide\Supports\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\TourGuide\Supports\Constants;

class PrivacyScope extends BaseScope
{
    protected User $user;

    public static function getAllowedPrivates(): array
    {
        return Arr::pluck(self::getPrivacyOptions(), 'value');
    }

    public static function getPrivacyOptions(): array
    {
        return [
            [
                'label' => __p('tourguide::phrase.privacy.everyone'),
                'value' => Constants::EVERYONE,
            ],
            [
                'label' => __p('tourguide::phrase.privacy.member_only'),
                'value' => Constants::MEMBER,
            ],
            [
                'label' => __p('tourguide::phrase.privacy.guest'),
                'value' => Constants::GUEST,
            ],
        ];
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $builder->whereIn($this->alias($table, 'privacy'), $this->getPrivates($this->getUser()));
    }

    protected function getPrivates(User $context): array
    {
        if ($context->isGuest()) {
            return [Constants::EVERYONE, Constants::GUEST];
        }

        return [Constants::EVERYONE, Constants::MEMBER];
    }
}
