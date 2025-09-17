<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1;

use Illuminate\Support\Arr;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\User\Models\UserShortcut;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getWebSettings(): array
    {
        $data = [
            UserShortcut::ENTITY_TYPE => [
                'sort_type' => [
                    UserShortcut::SORT_DEFAULT => __p('user::phrase.sort_automatically'),
                    UserShortcut::SORT_HIDE    => __p('user::phrase.hide'),
                    UserShortcut::SORT_PIN     => __p('user::phrase.pin_to_top'),
                ],
            ],
        ];

        return array_merge($data, $this->getSettingsRemoved());
    }

    public function getMobileSettings(): array
    {
        return $this->getSettingsRemoved();
    }

    protected function getSettingsRemoved(): array
    {
        $fields = $this->fieldRepository()->getFieldCollectionsByBasicInfoSection()
            ->pluck('is_active', 'field_name')->toArray();

        $mapping = [
            'enable_date_of_birth'       => 'birthdate',
            'enable_gender'              => 'gender',
            'enable_location'            => 'location',
            'enable_city'                => 'location',
            'enable_relationship_status' => 'relationship',
        ];

        return Arr::mapWithKeys($mapping, function ($value, $key) use ($fields) {
            return [$key => Arr::get($fields, $value, false)];
        });
    }

    protected function fieldRepository(): FieldRepositoryInterface
    {
        return resolve(FieldRepositoryInterface::class);
    }
}
