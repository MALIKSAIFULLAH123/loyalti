<?php

namespace MetaFox\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Core\Models\PrivacyMember;
use MetaFox\Core\Repositories\Contracts\PrivacyRepositoryInterface;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Class PrivacyTableSeeder.
 * @ignore
 * @codeCoverageIgnore
 */
class PrivacyTableSeeder extends Seeder
{
    /**
     * @var PrivacyRepositoryInterface
     */
    private $repository;

    /**
     * PrivacyDatabaseSeeder constructor.
     *
     * @param PrivacyRepositoryInterface $repository
     */
    public function __construct(PrivacyRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->seedPrivacyTables();
    }

    public function seedPrivacyTables(): void
    {
        $privacy = [
            [
                'privacy_id'   => MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID,
                'item_id'      => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                'item_type'    => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                'user_id'      => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                'user_type'    => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                'owner_id'     => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                'owner_type'   => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                'privacy_type' => MetaFoxPrivacy::PRIVACY_NETWORK_PUBLIC,
                'privacy'      => MetaFoxPrivacy::EVERYONE,
            ],
            [
                'privacy_id'   => MetaFoxPrivacy::NETWORK_MEMBERS_PRIVACY_ID,
                'item_id'      => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                'item_type'    => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                'user_id'      => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                'user_type'    => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                'owner_id'     => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                'owner_type'   => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                'privacy_type' => MetaFoxPrivacy::PRIVACY_NETWORK_MEMBER,
                'privacy'      => MetaFoxPrivacy::MEMBERS,
            ],
            [
                'privacy_id'   => MetaFoxPrivacy::NETWORK_FRIEND_OF_FRIENDS_ID,
                'item_id'      => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                'item_type'    => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                'user_id'      => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                'user_type'    => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                'owner_id'     => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_ID,
                'owner_type'   => MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE,
                'privacy_type' => MetaFoxPrivacy::PRIVACY_NETWORK_FRIEND_OF_FRIENDS,
                'privacy'      => MetaFoxPrivacy::FRIENDS_OF_FRIENDS,
            ],
        ];

        foreach ($privacy as $data) {
            $this->repository->getModel()->firstOrCreate($data, $data);
        }

        $data = [
            'user_id'    => 0,
            'privacy_id' => MetaFoxPrivacy::NETWORK_PUBLIC_PRIVACY_ID,
        ];
        PrivacyMember::query()->firstOrCreate($data, $data);
    }
}
