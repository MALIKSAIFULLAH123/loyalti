<?php

namespace MetaFox\User\Http\Resources\v1\UserRelation\Admin;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditForm.
 */
class EditForm extends CreateForm
{
    protected function prepare(): void
    {
        $imageId = $this->resource->image_file_id;
        if ($this->resource->relation_name && $imageId == null) {
            $imageId  = app('asset')->findByName($this->resource->relation_name)?->file_id;
        }

        $file = [
            'id'        => $imageId,
            'temp_file' => $imageId,
            'status'    => 'keep',
        ];

        $this->title(__p('user::phrase.edit_relation'))
            ->action("/admincp/user/relation/{$this->resource->id}")
            ->asPut()
            ->setValue([
                'is_active'  => $this->resource->is_active,
                'phrase_var' => Language::getPhraseValues($this->resource->phrase_var),
                'file'       => $file,
            ]);
    }

    protected function isEdit(): bool
    {
        return true;
    }
}
