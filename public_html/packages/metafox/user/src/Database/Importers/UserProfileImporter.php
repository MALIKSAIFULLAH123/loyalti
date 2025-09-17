<?php

namespace MetaFox\User\Database\Importers;

use Illuminate\Support\Carbon;
use MetaFox\Localize\Models\CountryChild;
use MetaFox\Localize\Models\Timezone;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Models\UserProfile as Model;
use MetaFox\User\Models\UserValue;
use MetaFox\User\Support\User as UserSupport;
use MetaFox\User\Support\UserBirthday;

/*
 * stub: packages/database/json-importer.stub
 */

class UserProfileImporter extends JsonImporter
{
    protected array $requiredColumns = ['user_id'];

    public function beforePrepare(): void
    {
        $this->remapRefs([
            '$user' => ['$oid'],
        ]);
    }

    public function getModelClass(): string
    {
        return Model::class;
    }

    public function afterPrepare(): void
    {
        $this->appendPhotoBundle('$avatar', 3, 'avatar_id', Model::class);
        $this->appendPhotoBundle('$cover', 3, 'cover_id', Model::class, '$user');
        $this->appendUserValueBundle();
    }

    public function processImport()
    {
        $this->remapRefs([
            '$user',
            '$inviteUser' => ['invite_user_id'],
            '$avatar.$id' => ['avatar_file_id'],
            '$cover.$id'  => ['cover_file_id'],
            '$gender',
            '$relation_with',
        ]);
        $this->remapCurrency();
        $this->remapTimezone();
        $this->remapCountryState();
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
        $this->upsertBatchEntriesInChunked(UserEntity::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $id            = $entry['user_id'];
        $birthday      = $entry['birthday'] ?? null;
        $birthdayMonth = null;
        if ($birthday !== null) {
            $birthdayParse = Carbon::parse($birthday);
            $birthdayMonth = $birthdayParse->month;
        }
        $this->addEntryToBatch(Model::class, [
            'id'                => $id,
            'full_phone_number' => $entry['full_phone_number'] ?? null,
            'birthday'          => $birthday,
            'birthday_doy'      => $entry['birthday_doy'] ?? null,
            'birthday_search'   => $entry['birthday_search'] ?? null,
            'birthday_month'    => $birthdayMonth,
            'country_iso'       => $entry['country_iso'] ?? null,
            //            'country_city_code'      => $entry['country_city_code'] ?? null,
            'country_state_id'       => $entry['country_state'] ?? 0,
            'address'                => $entry['city_location'] ?? '',
            'postal_code'            => $entry['postal_code'] ?? null,
            'dst_check'              => $entry['dst_check'] ?? null,
            'hide_tip'               => $entry['hide_tip'] ?? null,
            'status'                 => $entry['status'] ?? null,
            'footer_bar'             => $entry['footer_bar'] ?? null,
            'im_beep'                => $entry['im_beep'] ?? null,
            'im_hide'                => $entry['im_hide'] ?? null,
            'total_spam'             => $entry['total_spam'] ?? null,
            'previous_relation_type' => $entry['previous_relation_type'] ?? null,
            'relation'               => $entry['relation'] ?? null,
            'relation_with'          => $entry['relation_with_id'] ?? 0,
            'avatar_type'            => isset($entry['avatar_file_id']) ? 'photo' : null,
            'avatar_file_id'         => $entry['avatar_file_id'] ?? null,
            'cover_type'             => isset($entry['cover_file_id']) ? 'photo' : null,
            'cover_file_id'          => $entry['cover_file_id'] ?? null,
            'cover_photo_position'   => $entry['cover_photo_position'] ?? null,
            'invite_user_id'         => $entry['invite_user_id'] ?? null,
            'gender_id'              => $entry['gender_id'] ?? 0,
            'timezone_id'            => $entry['timezone_id'] ?? 0,
            'currency_id'            => $entry['currency_id'] ?? 'USD',
            'language_id'            => $entry['language_id'] ?? null,
            'created_at'             => $entry['created_at'] ?? null,
            'updated_at'             => $entry['updated_at'] ?? null,
        ]);

        $this->addEntryToBatch(UserEntity::class, [
            'id'             => $id,
            'avatar_type'    => isset($entry['avatar_file_id']) ? 'photo' : null,
            'avatar_file_id' => $entry['avatar_file_id'] ?? null,
            'gender'         => $entry['gender_id'] ?? 0,
            'entity_type'    => User::ENTITY_TYPE,
        ]);
    }

    public function remapTimezone()
    {
        $values   = $this->pickEntriesValue('timezone');
        $values[] = 'UTC';
        $map      = Timezone::query()
            ->whereIn('name', $values)
            ->pluck('id', 'name')
            ->toArray();
        foreach ($this->entries as &$entry) {
            $entry['timezone_id'] = $map[$entry['timezone'] ?? 'UTC'] ?? 0;
        }
    }

    public function remapCountryState()
    {
        $values = $this->pickEntriesValue('country_state_id');
        $map    = CountryChild::query()
            ->whereIn('name', $values)
            ->pluck('state_iso', 'name')
            ->toArray();
        foreach ($this->entries as &$entry) {
            if (!isset($entry['country_state_id'])) {
                continue;
            }
            $entry['country_state'] = $map[$entry['country_state_id']] ?? 0;
        }
    }

    private function appendUserValueBundle(): void
    {
        $column   = '$user';
        $callback = function (array &$output, &$entry) use ($column) {
            $user = $entry[$column] ?? null;
            if (!$user) {
                return;
            }
            $output[] = [
                '$id'   => 'user_value_birthday_' . $user,
                'name'  => 'user_profile_date_of_birth_format',
                'value' => UserBirthday::DATE_OF_BIRTH_SHOW_ALL,
                '$user' => $user,
            ];
            $output[] = [
                '$id'   => 'user_value_tagger_' . $user,
                'name'  => 'user_auto_add_tagger_post',
                'value' => UserSupport::AUTO_APPROVED_TAGGER_POST,
                '$user' => $user,
            ];
        };

        $this->exportBundled(UserValue::ENTITY_TYPE, 5, $column, $callback);
    }
}
