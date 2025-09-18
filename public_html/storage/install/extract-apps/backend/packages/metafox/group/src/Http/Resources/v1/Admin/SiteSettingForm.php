<?php

namespace MetaFox\Group\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Rules\MuteTimeRule;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SiteSettingForm.
 *
 * @property Model $resource
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'group';

        $vars = [
            'group.default_item_privacy',
            'group.display_cover_photo_within_gallery',
            'group.minimum_name_length',
            'group.maximum_name_length',
            'group.maximum_membership_question',
            'group.maximum_membership_question_option',
            'group.maximum_number_group_rule',
            'group.time_muted_member_option',
            'group.number_days_expiration_change_privacy',
            'group.number_hours_expiration_invite_code',
            'group.default_category',
            'group.invite_expiration_interval',
            'group.invite_expiration_role',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->title(__p('core::phrase.settings'))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic             = $this->addBasic();
        $maximumNameLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;
        $options           = $this->getCategoryRepository()->getCategoriesForForm();
        $basic->addFields(
            Builder::switch('group.display_cover_photo_within_gallery')
                ->label(__p('group::admin.display_cover_photo_within_gallery'))
                ->description(__p('group::admin.display_cover_photo_within_gallery_description')),
            Builder::choice('group.default_item_privacy')
                ->label(__p('core::phrase.default_item_privacy'))
                ->description(__p('core::phrase.default_item_privacy_description', ['type' => 'group']))
                ->multiple(false)
                ->required()
                ->options([
                    ['label' => __p('core::phrase.members_only'), 'value' => MetaFoxPrivacy::FRIENDS],
                    ['label' => __p('core::phrase.admins_only'), 'value' => MetaFoxPrivacy::CUSTOM],
                ]),
            Builder::text('group.minimum_name_length')
                ->label(__p('group::admin.minimum_name_length'))
                ->yup(
                    Yup::number()
                        ->int()
                        ->required()
                        ->unint()
                        ->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('group.maximum_name_length')
                ->label(__p('group::admin.maximum_name_length'))
                ->maxLength($maximumNameLength)
                ->yup(
                    Yup::number()
                        ->required()
                        ->unint()
                        ->when(
                            Yup::when('minimum_name_length')
                                ->is('$exists')
                                ->then(
                                    Yup::number()
                                        ->int()
                                        ->min(['ref' => 'minimum_name_length'])
                                        ->max($maximumNameLength)
                                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                                )
                        )
                        ->max($maximumNameLength)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('group.maximum_membership_question')
                ->label(__p('group::admin.maximum_membership_question'))
                ->description(__p('group::admin.maximum_membership_question_description'))
                ->yup(
                    Yup::number()
                        ->int()
                        ->min(0)
                        ->required()
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('group.maximum_membership_question_option')
                ->label(__p('group::admin.maximum_membership_question_option'))
                ->description(__p('group::admin.maximum_membership_question_option_description'))
                ->yup(
                    Yup::number()
                        ->int()
                        ->required()
                        ->min(2, __p('group::admin.maximum_membership_question_option_description'))
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('group.maximum_number_group_rule')
                ->label(__p('group::admin.maximum_number_group_rule_label'))
                ->description(__p('group::admin.maximum_number_group_rule_desc'))
                ->yup(
                    Yup::number()
                        ->int()
                        ->required()
                        ->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::tags('group.time_muted_member_option')
                ->returnKeyType('next')
                ->multiple(true)
                ->label(__p('group::admin.how_long_do_you_want_to_muted_member'))
                ->description(__p('group::admin.time_muted_member_desc'))
                ->disableSuggestion(),
            Builder::text('group.number_days_expiration_change_privacy')
                ->returnKeyType('next')
                ->label(__p('group::admin.number_days_expiration_change_privacy_label'))
                ->description(__p('group::admin.number_days_expiration_change_privacy_desc'))
                ->yup(Yup::number()
                    ->int()
                    ->min(0)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))),
            Builder::choice('group.default_category')
                ->label(__p('group::admin.group_default_category'))
                ->description(__p('group::admin.group_default_category_description'))
                ->disableClearable()
                ->required()
                ->options($options),
            Builder::text('group.number_hours_expiration_invite_code')
                ->returnKeyType('next')
                ->label(__p('group::admin.number_hours_expiration_invite_code_label'))
                ->description(__p('group::admin.number_hours_expiration_desc'))
                ->yup(Yup::number()
                    ->int()
                    ->min(0)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))),
            Builder::text('group.invite_expiration_interval')
                ->returnKeyType('next')
                ->label(__p('group::admin.invite_expiration_interval_label'))
                ->description(__p('group::admin.number_hours_expiration_desc'))
                ->yup(Yup::number()
                    ->int()
                    ->min(0)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))),
            Builder::text('group.invite_expiration_role')
                ->returnKeyType('next')
                ->label(__p('group::admin.number_hours_expiration_invite_role_label'))
                ->description(__p('group::admin.number_hours_expiration_desc'))
                ->yup(Yup::number()
                    ->int()
                    ->min(0)
                    ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))),
        );

        $this->addDefaultFooter(true);
    }

    /**
     * @throws ValidationException
     */
    public function validated(Request $request): array
    {
        $data  = $request->all();
        $rules = [
            'group.time_muted_member_option'   => ['array'],
            'group.time_muted_member_option.*' => [new MuteTimeRule()],
        ];

        $validator = Validator::make(
            $data,
            $rules,
            ['group.time_muted_member_option.*.regex' => __p('group::phrase.time_muted_member_option_regex_error')]
        );
        $validator->stopOnFirstFailure()->validate();

        return $data;
    }

    protected function getCategoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }
}
