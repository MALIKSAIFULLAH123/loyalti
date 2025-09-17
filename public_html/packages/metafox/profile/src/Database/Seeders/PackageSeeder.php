<?php

namespace MetaFox\Profile\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Profile;
use MetaFox\Profile\Models\Section;
use MetaFox\Profile\Models\Structure;
use MetaFox\Profile\Support\CustomField;

/**
 * stub: packages/database/seeder-database.stub.
 */

/**
 * Class PackageSeeder.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seederDefaultData();
        $this->seederBasicInfoFields();
    }

    protected function seederDefaultData(): void
    {
        if (Profile::query()->where('user_type', 'user')->exists()) {
            return;
        }

        Profile::query()->upsert([
            ['profile_type' => 'user', 'user_type' => 'user'],
        ], ['profile_type']);

        Section::query()->upsert([
            [
                'name'      => 'about',
                'is_system' => 0,
                'is_active' => 1,
            ],
            [
                'name'      => 'basic_info',
                'is_active' => 1,
                'is_system' => 1,
            ],
        ], ['name']);

        Field::query()->upsert([
            [
                'field_name'  => 'about_me',
                'type_id'     => 'string',
                'section_id'  => 1,
                'edit_type'   => CustomField::RICH_TEXT_EDITOR,
                'view_type'   => 'text',
                'is_required' => false,
                'is_active'   => true,
                'ordering'    => 1,
                'key'         => 'field_user_about_me',
            ],
            [
                'field_name'  => 'bio',
                'type_id'     => 'string',
                'section_id'  => 1,
                'edit_type'   => CustomField::RICH_TEXT_EDITOR,
                'view_type'   => 'text',
                'is_required' => false,
                'is_active'   => true,
                'ordering'    => 2,
                'key'         => 'field_user_bio',
            ],
            [
                'field_name'  => 'interest',
                'type_id'     => 'string',
                'section_id'  => 1,
                'edit_type'   => CustomField::RICH_TEXT_EDITOR,
                'view_type'   => 'text',
                'is_required' => false,
                'is_active'   => true,
                'ordering'    => 3,
                'key'         => 'field_user_interest',
            ],
            [
                'field_name'  => 'hobbies',
                'type_id'     => 'string',
                'section_id'  => 1,
                'edit_type'   => CustomField::RICH_TEXT_EDITOR,
                'view_type'   => 'text',
                'is_required' => false,
                'is_active'   => true,
                'ordering'    => 4,
                'key'         => 'field_user_hobbies',
            ],
        ], ['field_name']);

        $sectionIds = Section::query()->pluck('id')->toArray();
        $profile    = Profile::query()->where('user_type', 'user')->first();
        $data       = [];
        foreach ($sectionIds as $sectionId) {
            $data[] = [
                'section_id' => $sectionId,
                'profile_id' => $profile->id,
            ];
        }

        Structure::query()->insert($data);
    }

    protected function seederBasicInfoFields(): void
    {
        $section = Section::query()->where('name', 'basic_info')->first();
        if (!$section instanceof Section) {
            return;
        }

        $sectionId = $section->entityId();

        if (Field::query()->where('section_id', $sectionId)->exists()) {
            return;
        }
        $isBasicFieldRequired = Settings::get('user.require_basic_field', false);
        $enableRelationship   = Settings::get('user.enable_relationship_status', false);
        $enableDateOfBirth    = Settings::get('user.enable_date_of_birth', false);
        $enableGender         = Settings::get('user.enable_gender', false);
        $enableLocation       = Settings::get('user.enable_location', false);

        $fields = [
            [
                'field_name'  => CustomField::RELATIONSHIP_FIELD_NAME,
                'type_id'     => 'string',
                'section_id'  => $sectionId,
                'edit_type'   => CustomField::CHOICE,
                'view_type'   => 'text',
                'is_required' => false,
                'is_register' => false,
                'is_active'   => $enableRelationship,
                'ordering'    => 1,
                'extra'       => json_encode([
                    'icon'             => 'ico-heart-o',
                    'disable_register' => true,
                ]),
                'key'         => 'field_user_relationship',
            ],
            [
                'field_name'  => 'gender',
                'type_id'     => 'string',
                'section_id'  => $sectionId,
                'edit_type'   => CustomField::CHOICE,
                'view_type'   => 'text',
                'is_required' => $isBasicFieldRequired,
                'is_register' => $enableGender,
                'is_active'   => true,
                'ordering'    => 2,
                'extra'       => json_encode([
                    'icon' => 'ico-sex-unknown',
                ]),
                'key'         => 'field_user_gender',
            ],
            [
                'field_name'  => 'birthdate',
                'type_id'     => 'string',
                'section_id'  => $sectionId,
                'edit_type'   => CustomField::BASIC_DATE,
                'view_type'   => 'text',
                'is_required' => $isBasicFieldRequired,
                'is_active'   => true,
                'is_register' => $enableDateOfBirth,
                'ordering'    => 3,
                'extra'       => json_encode([
                    'icon' => 'ico-birthday-cake',
                ]),
                'key'         => 'field_user_birthdate',
            ],
            [
                'field_name'  => 'address',
                'type_id'     => 'string',
                'section_id'  => $sectionId,
                'edit_type'   => CustomField::TEXT,
                'view_type'   => 'text',
                'is_required' => false,
                'is_register' => false,
                'is_active'   => true,
                'ordering'    => 5,
                'extra'       => json_encode([
                    'disable_register' => true,
                    'icon'             => 'ico-checkin-o',
                ]),
                'key'         => 'field_user_address',
            ],
            [
                'field_name'  => 'location',
                'type_id'     => 'string',
                'section_id'  => $sectionId,
                'edit_type'   => CustomField::TEXT,
                'view_type'   => 'text',
                'is_required' => $isBasicFieldRequired,
                'is_active'   => true,
                'is_register' => $enableLocation,
                'ordering'    => 4,
                'extra'       => json_encode([
                    'icon' => 'ico-globe-o',
                ]),
                'key'         => 'field_user_location',
            ],
        ];

        Field::query()->upsert($fields, ['field_name']);
    }
}
