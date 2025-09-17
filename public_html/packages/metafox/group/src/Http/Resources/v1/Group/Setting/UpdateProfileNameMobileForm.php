<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\RegexRule\Support\Facades\Regex;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateProfileNameMobileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateProfileNameMobileForm extends AbstractForm
{
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p("group::phrase.label.profile_name"))
            ->action("group/{$this->resource->entityId()}")
            ->asPut()
            ->secondAction('@updatedItem/group')
            ->setValue([
                'vanity_url' => $this->resource->profile_name ?? '',
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addField(
                Builder::text('vanity_url')
                    ->label(__p('core::phrase.url'))
                    ->placeholder(__p('core::phrase.url'))
                    ->description(__p('group::phrase.description_edit_group_url'))
                    ->setAttribute('contextualDescription', url_utility()->makeApiFullUrl(''))
                    ->findReplace([
                        'find'    => MetaFoxConstant::SLUGIFY_FILTERS,
                        'replace' => MetaFoxConstant::SLUGIFY_FILTERS_REPLACE,
                    ])
                    ->yup(Yup::string()
                        ->maxLength(100)
                        ->matches(Regex::getRegexSetting('user_name'))
                        ->setError('matches', __p(Settings::get('regex.user_name_regex_error_message')))),
            );
    }
}
