<?php

namespace MetaFox\Storage\Support;

use MetaFox\Form\Html\Choice;
use MetaFox\Yup\Yup;

class SelectDiskVisibility extends Choice
{
    public function initialize(): void
    {
        parent::initialize();

        $visibilityOptions = [
            ['value' => 'public', 'label' => __p('storage::phrase.visibility_public')],
            ['value' => 'private', 'label' => __p('storage::phrase.visibility_private')],
        ];

        $this->required()
            ->name('visibility')
            ->options($visibilityOptions)
            ->label(__p('storage::phrase.storage_visibility'))
            ->yup(Yup::string());
    }
}
