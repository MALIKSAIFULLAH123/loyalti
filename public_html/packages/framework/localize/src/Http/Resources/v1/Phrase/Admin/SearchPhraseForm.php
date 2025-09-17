<?php

namespace MetaFox\Localize\Http\Resources\v1\Phrase\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Localize\Models\Phrase as Model;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Localize\Support\Browse\Scopes\Phrase\ViewScope;
use MetaFox\Platform\Facades\Settings;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchPhraseForm.
 * @property Model $resource
 */
class SearchPhraseForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('/phrase')
            ->acceptPageParams(['q', 'locale', 'group', 'package_id'])
            ->setValue([
                'locale' => Settings::get('localize.default_locale', 'en'),
                'view'   => ViewScope::VIEW_DEFAULT,
            ]);
    }

    protected function initialize(): void
    {
        $basic         = $this->addBasic(['variant' => 'horizontal']);

        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm(),
            Builder::selectLocale('locale')
                ->forAdminSearchForm()
                ->disableClearable()
                ->label(__p('localize::phrase.language')),
            Builder::selectPackage('package_id')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.package_name')),
            Builder::choice('view')
                ->forAdminSearchForm()
                ->disableClearable()
                ->options(ViewScope::getFilterOptions())
                ->showWhen([
                    'and',
                    ['neq', 'locale', 'en'],
                ]),
            Builder::submit()
                ->forAdminSearchForm()
        );
    }
}
