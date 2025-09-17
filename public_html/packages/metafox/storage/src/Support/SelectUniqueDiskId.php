<?php

namespace MetaFox\Storage\Support;

use MetaFox\Form\Html\Text;
use MetaFox\Yup\Yup;

class SelectUniqueDiskId extends Text
{
    public function initialize(): void
    {
        parent::initialize();

        $excludes = array_keys(config('filesystems.disks'));

        $this->required()
            ->label(__p('storage::phrase.unique_disk_id'))
            ->description(__p('storage::phrase.unique_disk_id_desc'))
            ->yup(Yup::string()
                ->maxLength(16)
                ->notOneOf($excludes, __p('storage::validation.unique_disk_id_existed'))
                ->matches('^([\w\-]+)$', __p('storage::validation.unique_disk_id_invalid_format'))
                ->required());
    }
}
