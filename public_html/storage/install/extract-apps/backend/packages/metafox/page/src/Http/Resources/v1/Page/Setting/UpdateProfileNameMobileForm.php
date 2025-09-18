<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder as Builder;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageRepositoryInterface;
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
    public function boot(PageRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('page::phrase.label.profile_name'))
            ->action("page/{$this->resource->entityId()}")
            ->secondAction('page/updatePageInfo')
            ->asPut()
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
                    ->description(__p('page::phrase.description_edit_page_url'))
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
