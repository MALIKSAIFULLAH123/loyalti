<?php

namespace MetaFox\Group\Contracts;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as ContractUser;

interface SupportContract
{
    /**
     * @param int $id
     *
     * @return Group|null
     */
    public function getGroup(int $id): ?Group;

    /**
     * @param Group $group
     *
     * @return bool
     */
    public function mustAnswerMembershipQuestion(Group $group): bool;

    /**
     * @param Group $group
     *
     * @return bool
     */
    public function mustAcceptGroupRule(Group $group): bool;

    /**
     * @param Group $group
     *
     * @return Collection|null
     */
    public function getQuestions(Group $group): ?Collection;

    /**
     * @return int
     */
    public function getMaximumNumberGroupRule(): int;

    /**
     * @return int
     */
    public function getMaximumMembershipQuestion(): int;

    /**
     * @return int
     */
    public function getMaximumNumberMembershipQuestionOption(): int;

    /**
     * @return array
     */
    public function getListTypes(): array;

    /**
     * @return array
     */
    public function getPrivacyList(): array;

    /**
     * @param string $content
     *
     * @return array
     */
    public function getMentions(string $content): array;

    /**
     * @param array $ids
     *
     * @return Collection
     */
    public function getGroupsForMention(array $ids): Collection;

    /**
     * @param User $user
     *
     * @return Builder
     */
    public function getGroupBuilder(User $user): Builder;

    /**
     * @param ContractUser $user
     *
     * @return Builder
     */
    public function getPublicGroupBuilder(User $user): Builder;

    /**
     * @param ContractUser $context
     * @param ContractUser $user
     *
     * @return bool
     */
    public function isFollowing(ContractUser $context, ContractUser $user): bool;

    /**
     * @param Group $group
     *
     * @return array
     */
    public function getProfileMenuSettings(Group $group): array;

    /**
     * @param Group $group
     *
     * @return array
     */
    public function getTabNameDefaults(Group $group): array;

    /**
     * @return array
     */
    public function getAllowApiRules(): array;

    /**
     * @param string $resolution
     *
     * @return array
     */
    public function getInfoSettingsSupportByResolution(string $resolution): array;

    /**
     * @param Group $group
     *
     * @return int
     */
    public function getTotalMemberByPrivacy(Group $group): int;

    /**
     * @param Group $group
     *
     * @return string
     */
    public function getCacheKeyDefaultTabActive(Group $group): string;

    /**
     * @param int $statusId
     * @return array
     */
    public function statusInviteInfo(int $statusId): array;
}
