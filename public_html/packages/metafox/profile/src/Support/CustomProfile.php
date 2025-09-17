<?php

namespace MetaFox\Profile\Support;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Contracts\CustomProfileInterface;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Section;
use MetaFox\Profile\Models\Value;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;
use MetaFox\Profile\Repositories\ValueRepositoryInterface;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Support\Facades\User as UserFacades;
use MetaFox\User\Support\Facades\UserBirthday;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Traits\UserLocationTrait;

class CustomProfile implements CustomProfileInterface
{
    use UserLocationTrait;
    public function __construct(
        protected FieldRepositoryInterface $fieldRepository,
        protected SectionRepositoryInterface $sectionRepository,
        protected ValueRepositoryInterface $valueRepository,
        protected RoleRepositoryInterface $roleRepository,
    ) {
    }

    /**
     * @inheritDoc
     * @throws AuthenticationException
     */
    public function getProfileValues(User $user, array $attributes): array
    {
        $sectionType = Arr::get($attributes, 'section_type', CustomField::SECTION_TYPE_USER);
        $query       = $this->sectionRepository->buildQuerySection($user, $attributes);

        if (CustomField::SECTION_TYPE_USER === $sectionType) {
            $query->orWhere('is_system', 1);
        }

        $sections = $query->get();

        $output          = [];
        $context         = user();
        $profileSettings = UserPrivacy::hasAccessProfileSettings($context, $user);

        if ($sections->isEmpty()) {
            return $output;
        }

        foreach ($sections as $section) {
            /** @var Section $section */
            $fieldsValue = match ($section->is_system) {
                true  => $this->handleSectionSystem($context, $user, $section),
                false => $this->fieldRepository->getFieldsValueBySection($user, $section, $attributes),
            };

            if (empty($fieldsValue)) {
                continue;
            }
            $data = [
                'label'       => $section->label,
                'description' => $section->description,
                'fields'      => $fieldsValue,
            ];

            if ($section->is_system) {
                if (!$profileSettings['profile_basic_info']) {
                    continue;
                }

                Arr::set($data, 'component', 'layout.section.icon_list');
            }

            $output[$section->name] = $data;
        }

        return $output;
    }

    /**
     * @param User  $user
     * @param array $attributes
     *
     * @return array
     */
    public function denormalize(User $user, array $attributes): array
    {
        $result          = [];
        $context         = Auth::user();
        $fieldCollection = $this->fieldRepository->getFieldCollections($user, $context, $attributes);
        $customValues    = $this->valueRepository->getValuesByUser($user);

        if ($fieldCollection->isEmpty()) {
            return $result;
        }

        if ($customValues->isEmpty()) {
            return $result;
        }

        foreach ($customValues as $customValue) {
            /** @var $customValue Value */
            $fieldId = $customValue->field_id;
            $field   = $fieldCollection->where('id', $fieldId)->first();

            if (!$field instanceof Field) {
                continue;
            }

            $name = $field->name;

            if (!$name) {
                continue;
            }

            $value = $this->valueRepository->handleFieldValue($field, $customValue, $attributes);

            if ($value === null) {
                continue;
            }

            Arr::set($result, $name, $value);
        }

        return $result;
    }

    /**
     * @param User  $user
     * @param array $input
     * @param array $attributes
     *
     * @return void
     */
    public function saveValues(User $user, array $input, array $attributes): void
    {
        $fieldCollection = $this->getFieldsActiveCollectionByType($user, $attributes);
        if ($fieldCollection->isEmpty()) {
            return;
        }

        foreach ($fieldCollection as $field) {
            /** @var $field Field */
            if (!Arr::has($input, $field->name)) {
                continue;
            }

            $value    = Arr::get($input, $field->name);
            $editType = $field?->edit_type;

            if (null === $editType) {
                continue;
            }

            Arr::set($dataValue, 'field_id', $field->entityId());
            Arr::set($dataValue, 'field_value', $value);

            $item  = $this->valueRepository->createValue($user, $dataValue);
            $value = $this->transformFieldValue($editType, $value);

            $item->fill($value);

            $item->saveQuietly();
        }

        $this->fieldRepository->clearCache();
    }

    private function transformFieldValue(string $editType, mixed $value): array
    {
        $data = [
            'field_value_text' => $value,
        ];

        if (CustomField::MULTI_CHOICE === $editType) {
            Arr::set($data, 'field_value_text', json_encode($value));
            Arr::set($data, 'optionsData', $value);
        }

        if (CustomField::BASIC_DATE === $editType && $value && !preg_match(CustomField::BASIC_DATE_REGEX, $value)) {
            Arr::set($data, 'field_value_text', Carbon::parse($value)
                ->setTimezone(MetaFox::clientTimezone())
                ->format(CustomField::BASIC_DATE_FORMAT));
        }

        return $data;
    }

    /**
     * @param User|null $user
     * @param array     $attributes
     *
     * @return Collection
     *                    Doesn't query the field by role
     */
    private function getFieldsActiveCollectionByType(?User $user, array $attributes): Collection
    {
        $sectionType = Arr::get($attributes, 'section_type');
        $cacheName   = 'fields_active_by_' . $sectionType;

        return Cache::rememberForever($cacheName, function () use ($user, $attributes) {
            $tableSection = (new Section())->getTable();
            $tableField   = $this->fieldRepository->getModel()->getTable();
            $query        = $this->fieldRepository->getBuildQuery($user, []);

            $query->select("$tableField.*");
            $query->leftJoin($tableSection, "$tableSection.id", '=', "$tableField.section_id");
            $query = $this->sectionRepository->buildQueryProfile($query, $attributes)
                ->where("$tableSection.is_active", MetaFoxConstant::IS_ACTIVE);

            return $query->orderBy("$tableField.ordering")->get();
        });
    }

    public function handleSectionSystem(User $context, User $resource, Section $section, array $attributes = []): array
    {
        $fieldsValue      = [];
        $fieldCollections = $this->fieldRepository->getFieldCollections($resource, $context, ['section_id' => $section->entityId()]);

        foreach ($fieldCollections as $field) {
            /** @var $field Field */
            if (!$field->is_active) {
                continue;
            }

            $methodName = 'build' . Str::studly($field->field_name) . 'Field';
            $data       = $this->$methodName($context, $resource, $attributes);
            $extra      = $field->extra;
            Arr::set($data, 'label', $field->label);
            Arr::set($data, 'icon', Arr::get($extra, 'icon', ''));

            $fieldsValue[$field->field_name] = $data;
        }

        return array_merge($fieldsValue, $this->buildDefaultField($resource));
    }

    protected function buildRelationshipField(User $context, User $resource, array $attributes = []): array
    {
        if (!$resource instanceof UserModel) {
            return [];
        }

        $profile = $resource->profile;

        return [
            'value'      => $profile->relationship_text,
            'value_text' => $profile->relationship_text,
        ];
    }

    protected function buildGenderField(User $context, User $resource, array $attributes = []): array
    {
        if (!$resource instanceof UserModel) {
            return [];
        }

        $profile = $resource->profile;

        return [
            'value'      => UserFacades::getGender($profile),
            'value_text' => UserFacades::getGender($profile),
        ];
    }

    protected function buildBirthdateField(User $context, User $resource, array $attributes = []): array
    {
        if (!$resource instanceof UserModel) {
            return [];
        }

        return [
            'value'      => UserBirthday::getFormattedBirthday($context, $resource),
            'value_text' => UserBirthday::getFormattedBirthday($context, $resource),
        ];
    }

    protected function buildLocationField(User $context, User $resource, array $attributes = []): array
    {
        if (!$resource instanceof UserModel) {
            return [];
        }

        if (!UserPrivacy::hasAccess($context, $resource, 'profile.view_location')) {
            return [];
        }

        return [
            'value'      => $this->getLocationValue($context, $resource),
            'value_text' => $this->getLocationValue($context, $resource),
        ];
    }

    protected function buildAddressField(User $context, User $resource, array $attributes = []): array
    {
        if (!$resource instanceof UserModel) {
            return [];
        }

        $profile = $resource?->profile;

        return [
            'value'      => ban_word()->clean($profile?->address) ?? null,
            'value_text' => ban_word()->clean($profile?->address) ?? null,
        ];
    }

    protected function buildDefaultField(User $resource): array
    {
        if (!$resource instanceof UserModel) {
            return [];
        }

        $profile = $resource->profile;

        return [
            'member_since' => [
                'label'      => __p('user::phrase.member_since'),
                'value'      => Carbon::parse($profile->created_at),
                'type'       => 'datetime',
                'value_text' => Carbon::parse($profile->created_at),
                'format'     => 'L',
                'as'         => 'Date',
                'icon'       => 'ico-clock-o',
            ],
            'membership' => [
                'icon'       => 'ico-calendar-o',
                'label'      => __p('user::phrase.membership'),
                'value'      => $resource->roles->pluck('name'),
                'value_text' => $resource->roles->pluck('name'),
            ],
        ];
    }
}
