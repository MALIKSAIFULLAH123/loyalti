<?php

namespace MetaFox\Session\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Session\Support\CheckDefaultSessionDriverField;

class UpdateFileSessionForm extends AbstractForm
{
    protected function prepare(): void
    {
        $values = [
            'files' => config('session.files'),
        ];

        $this->title(__p('session::files.edit_settings'))
            ->action('admincp/session/store/file')
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('files')
                    ->label(__p('session::phrase.files_label'))
                    ->description(__p('session::phrase.files_desc'))
                    ->readOnly(),
                new CheckDefaultSessionDriverField(),
            );

        $this->addDefaultFooter(true);
    }

    #[ArrayShape(['session.driver' => 'string'])]
    public function validated(Request $request): array
    {
        $result = [];
        $data   = $request->validate([
            'files'       => 'required|string',
            'set_default' => 'sometimes|boolean|nullable',
        ]);

        $isDefault = $data['is_default'];

        if ($isDefault) {
            $result['session.driver'] = 'files';
        }

        return $result;
    }
}
