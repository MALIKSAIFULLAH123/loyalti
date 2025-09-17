<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;

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
        if (!Schema::hasTable('phrases')) {
            return;
        }
        $phrases = [
            'all_users_in_the_channel_can_write_new_messages',
            'all_users_in_the_group_can_write_new_messages',
            'archived_group',
            'archived_groups',
            'archive_group_successfully',
            'archive_room_description',
            'are_you_sure_you_want_to_leave_the_group',
            'are_you_sure_you_want_to_leave_the_room',
            'are_you_sure_you_want_to_unpin_this_content',
            'broadcast_channel',
            'broadcast_channel_description',
            'broadcast_group',
            'broadcast_group_description',
            'chatplus',
            'chatplus_app_released_new_description',
            'chatplus_server_released_new_description',
            'chatplus_sync_settings_success_description',
            'chatplus_sync_user_success_description',
            'chatplus_there_are_total_users_to_sync',
            'create_channel',
            'create_chat_group_successfully',
            'create_group',
            'create_group_with',
            'creating_channel',
            'creating_group',
            'delete_group_warning',
            'delete_room_warning',
            'error_invalid_channel',
            'error_invalid_channel_start_with_chars',
            'group_actions',
            'group_by_favorites',
            'group_by_type',
            'group_changed_announcement',
            'group_changed_description',
            'group_changed_privacy',
            'group_changed_topic',
            'group_files',
            'group_info',
            'group_info_edit',
            'group_information',
            'group_members',
            'group_name_changed',
            'groups',
            'has_joined_the_channel',
            'has_joined_the_conversation',
            'has_joined_the_group',
            'has_left_the_channel',
            'has_left_the_group',
            'hide_group',
            'hide_room',
            'invalid-secret-code [invalid-secret-code]',
            'is_a_valid_chatplus_instance',
            'is_not_a_valid_chatplus_instance',
            'leave_channel',
            'leave_group',
            'leave_group_chat',
            'leave_group_successfully',
            'leaving_group',
            'leaving_room',
            'message_pinned',
            'message_unarchive_chat',
            'microphone_permission',
            'microphone_permission_message',
            'new_group',
            'new_group_conversation',
            'notify_active_in_this_group',
            'notify_active_in_this_room',
            'notify_active_users_in_this_room_is_not_allowed',
            'notify_all_in_this_group',
            'notify_all_in_this_room',
            'notify_all_in_this_room_is_not_allowed',
            'number_people_in_group',
            'p_chatplus_anonymous',
            'p_chatplus_can_delete_own_message',
            'p_chatplus_chat_bot',
            'p_chatplus_chat_visibility',
            'p_chatplus_enable_broadcast_chanel',
            'p_chatplus_enable_discussion',
            'p_chatplus_enable_private_chanel',
            'p_chatplus_enable_readonly_chanel',
            'p_chatplus_ios_apn_key',
            'p_chatplus_ios_apn_key_id',
            'p_chatplus_ios_apn_team_id',
            'p_chatplus_ios_bundle_id',
            'p_chatplus_jitsi_domain_option',
            'p_chatplus_livechat_agent',
            'p_chatplus_livechat_guest',
            'p_chatplus_livechat_manager',
            'p_chatplus_permissions',
            'p_chatplus_permissions_updated',
            'p_chatplus_private_code',
            'p_chatplus_room_leader',
            'p_chatplus_room_moderator',
            'p_chatplus_room_owner',
            'p_chatplus_server',
            'private_channel',
            'private_group',
            'private_groups',
            'public_channel',
            'public_channel_description',
            'public_channels',
            'public_group',
            'public_group_chat',
            'public_group_chats',
            'public_groups',
            'read_only_channel',
            'read_only_group',
            'remove_pin_message_successfully',
            'remove_role',
            'remove_room_leader',
            'remove_room_moderator',
            'remove_room_owner',
            'remove_room_user',
            'remove_star',
            'remove_star_message_successfully',
            'required_chatplus_jitsi_application_id_and_secret',
            'required_chatplus_jitsi_domain',
            'this_group_is_archived',
            'this_group_is_blocked',
            'this_group_is_read_only',
            'this_group_is_readonly',
            'this_room_is_blocked',
            'this_room_is_read_only',
            'type_the_channel_name_here',
            'type_the_group_name_here',
            'user_has_joined_the_group',
            'user_has_left_the_group',
            'user_message_pinned',
            'user_message_unpinned',
            'user_pinned_message',
            'user_r',
            'user_removed_by',
            'user_replied_to_author',
            'user_room_archived',
            'user_room_archived_topic',
            'user_room_changed_announcement',
            'user_room_changed_avatar',
            'user_room_changed_description',
            'user_room_changed_privacy',
            'user_room_changed_topic',
            'user_room_name_changed',
            'user_room_removed_read_only',
            'user_room_set_read_only',
            'user_room_unarchived',
            'user_room_unarchived_topic',
            'user_ru',
            'user_subscription_role_added',
            'user_subscription_role_removed',
            'user_au',
            'user_uj',
            'user_ul',
            'user_unpinned_message',
            'user_user_muted',
            'user_user_unmuted',
            'user_was_removed',
            'user_was_set_as_role',
            'user_was_set_role_by_',
            'welcome_to_chatplus',
            'you_room_archived_topic',
            'you_room_changed_announcement',
            'you_room_changed_description',
            'you_room_changed_privacy',
            'you_room_changed_topic',
            'you_room_name_changed',
            'you_room_unarchived_topic',
            'you_message_unpinned',
            'you_message_pinned',
            'you_have_been_muted_in_this_group',
            'you_need_to_have_at_least_2_friends',
            'you_r',
            'your_build_video_bride_server',
            'you_ru',
            'you_au',
            'you_ul',
            'you_user_muted',
            'you_user_unmuted',
        ];
        $deletePhrases = array_map(function ($phrase) {
            return 'chat::web.' . $phrase;
        }, $phrases);

        $this->getPhraseRepository()->getModel()
            ->newModelQuery()
            ->whereIn('key', $deletePhrases)
            ->get()
            ->collect()
            ->each(function (Phrase $phrase) {
                $phrase->delete();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    public function getPhraseRepository(): PhraseRepositoryInterface
    {
        return resolve(PhraseRepositoryInterface::class);
    }
};
