<?php

namespace MetaFox\User\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\User\Models\UserRelation;

/**
 * Class UserRelationTableSeeder.
 * @codeCoverageIgnore
 * @ignore
 */
class UserRelationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (UserRelation::query()->exists()) {
            return;
        }

        $data = [
            [
                'phrase_var'    => 'user::relation.unknown_status',
                'is_custom'     => 0,
                'is_active'     => 1,
                'image_file_id' => null,
                'relation_name' => null,
            ],
            [
                'phrase_var'    => 'user::relation.rlt_single',
                'is_active'     => 1,
                'is_custom'     => 0,
                'image_file_id' => app('asset')->findByName('rlt_single')?->file_id,
                'relation_name' => 'rlt_single',
            ],
            [
                'phrase_var'    => 'user::relation.rlt_engage',
                'is_active'     => 1,
                'is_custom'     => 0,
                'image_file_id' => app('asset')->findByName('rlt_engage')?->file_id,
                'relation_name' => 'rlt_engage',
            ],
            [
                'phrase_var'    => 'user::relation.rlt_married',
                'is_active'     => 1,
                'is_custom'     => 0,
                'image_file_id' => app('asset')->findByName('rlt_married')?->file_id,
                'relation_name' => 'rlt_married',
            ],
            [
                'phrase_var'    => 'user::relation.rlt_complicated',
                'is_active'     => 1,
                'is_custom'     => 0,
                'image_file_id' => app('asset')->findByName('rlt_complicated')?->file_id,
                'relation_name' => 'rlt_complicated',
            ],
            [
                'phrase_var'    => 'user::relation.rlt_open',
                'is_active'     => 1,
                'is_custom'     => 0,
                'image_file_id' => app('asset')->findByName('rlt_open')?->file_id,
                'relation_name' => 'rlt_open',
            ],
            [
                'phrase_var'    => 'user::relation.rlt_widow',
                'is_active'     => 1,
                'is_custom'     => 0,
                'image_file_id' => app('asset')->findByName('rlt_widow')?->file_id,
                'relation_name' => 'rlt_widow',
            ],
            [
                'phrase_var'    => 'user::relation.rlt_separated',
                'is_active'     => 1,
                'is_custom'     => 0,
                'image_file_id' => app('asset')->findByName('rlt_separated')?->file_id,
                'relation_name' => 'rlt_separated',
            ],
            [
                'phrase_var'    => 'user::relation.rlt_divorced',
                'is_active'     => 1,
                'is_custom'     => 0,
                'image_file_id' => app('asset')->findByName('rlt_divorced')?->file_id,
                'relation_name' => 'rlt_divorced',
            ],
            [
                'phrase_var'    => 'user::relation.rlt_relationship',
                'is_active'     => 1,
                'is_custom'     => 0,
                'image_file_id' => app('asset')->findByName('rlt_relationship')?->file_id,
                'relation_name' => 'rlt_relationship',
            ],
        ];
        UserRelation::query()->insert($data);
    }
}
