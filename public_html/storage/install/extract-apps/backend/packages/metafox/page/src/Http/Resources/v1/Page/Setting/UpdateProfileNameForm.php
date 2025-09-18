<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Setting;

use MetaFox\Form\AbstractForm;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\RegexRule\Support\Facades\Regex;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Page\Models\Page as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateProfileNameForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateProfileNameForm extends AbstractForm
{
    public function boot(PageRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->action("page/{$this->resource->entityId()}")
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
                    ->label(__p('core::phrase.username'))
                    ->placeholder(__p('core::phrase.username'))
                    ->maxLength(100)
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

        $this->addFooter(['separator' => false])
            ->addFields(
                Builder::submit()
                    ->disableWhenClean()
                    ->label(__p('core::phrase.save_changes')),
                Builder::cancelButton(),
            );
    }
}
