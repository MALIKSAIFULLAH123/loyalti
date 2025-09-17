<?php

namespace MetaFox\Localize\Http\Resources\v1\Phrase\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use Illuminate\Support\Str;
use MetaFox\Core\Support\Facades\Language;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class TranslatePhraseForm.
 * @property ?string $resource
 * @ignore
 * @codeCoverageIgnore
 */
class TranslatePhraseForm extends AbstractForm
{
    public function boot(Request $request): void
    {
        if (!($key = $request->get('key'))) {
            return;
        }

        $this->resource = $key;
    }

    protected function prepare(): void
    {
        $key = $this->resource;

        Language::disableEditMode();
        Config::set('localize.disable_translation', false);

        $text = $key ? app('translator')->get($key) : $key;
        if ($text === $key) {
            // should be trim phrase.
            $text = preg_replace('#^([\w-]+)::([\w-]+).([\w-]+)#', '$3', $text);

            $text = Str::headline($text);
        }

        $this->title(__p('core::phrase.edit'))
            ->action('admincp/phrase/translate')
            ->asPost()
            ->setValue([
                'translation_key'  => $this->resource,
                'translation_text' => $text,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('translation_key')
                ->required()
                ->readOnly()
                ->label(__p('localize::phrase.key_name')),
            Builder::textArea('translation_text')
                ->label(__p('localize::phrase.translation')),
        );

        $this->addDefaultFooter(true);
    }
}
