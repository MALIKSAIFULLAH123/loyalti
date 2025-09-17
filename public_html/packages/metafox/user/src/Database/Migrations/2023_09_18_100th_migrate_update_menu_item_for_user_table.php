<?php

use Illuminate\Database\Migrations\Migration;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $detailOrdering = [
            'feature'        => 1,
            'delete_feature' => 1,
            'edit_profile'   => 2,
            'update_cover'   => 3,
            'update_avatar'  => 4,
            'remove_cover'   => 5,
            'report'         => 9,
            'block'          => 10,
        ];
        $detailDelete = [
            'sponsor_in_feed',
            'unfollow',
            'delete_sponsor_in_feed',
            'sponsor',
            'delete_sponsor',
            'follow',
            'add_friend',
            'cancel_request',
            'confirm_request',
            'delete_request',
            'edit_list',
            'unfriend',
            'gift_points',
        ];
        $itemDelete = [
            'sponsor_in_feed',
            'edit_profile',
            'delete_sponsor_in_feed',
            'sponsor',
            'delete_sponsor',
            'feature',
            'delete_feature',
            'block',
            'update_cover',
            'update_avatar',
            'remove_cover',
            'report',
        ];
        $itemOrdering = [
            'follow'   => 6,
            'unfollow' => 6,
            'unfriend' => 7,
        ];

        $profileDelete = [
            'message',
            'poke',
            'block',
            'unblock',
            'edit_profile',
            'feature',
            'unfeature',
            'gift_points',
            'report',
        ];

        $profileOrdering = [
            'add_friend'      => 1,
            'cancel_request'  => 2,
            'confirm_request' => 3,
            'delete_request'  => 4,
            'edit_list'       => 5,
            'follow'          => 6,
            'unfollow'        => 6,
            'un_friend'       => 7,
        ];

        $this->handleMenuItem('user.user.detailActionMenu', $detailDelete, $detailOrdering);
        $this->handleMenuItem('user.user.itemActionMenu', $itemDelete, $itemOrdering);
        $this->handleMenuItem('user.user.profileActionMenu', $profileDelete, $profileOrdering);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    protected function handleMenuItem(string $menuName, array $deleteAction, array $updateOrdering)
    {
        $menuItem = new \MetaFox\Menu\Models\MenuItem();

        $menuItem->newModelQuery()->where('menu', $menuName)
            ->where('resolution', 'mobile')
            ->whereIn('name', $deleteAction)->delete();

        foreach ($updateOrdering as $key => $value) {
            $menuItem->newModelQuery()
                ->where('menu', $menuName)
                ->where('resolution', 'mobile')
                ->where('name', $key)
                ->update([
                    'ordering' => $value,
                ]);
        }
    }
};
