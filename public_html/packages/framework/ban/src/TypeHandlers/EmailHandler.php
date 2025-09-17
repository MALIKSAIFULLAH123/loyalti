<?php

namespace MetaFox\Ban\TypeHandlers;

use MetaFox\Ban\Supports\Constants;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

class EmailHandler extends AbstractTypeHandler
{
    public function getType(): string
    {
        return Constants::BAN_EMAIL_TYPE;
    }

    public function getFormTitle(): string
    {
        return __p('ban::phrase.add_new_email');
    }

    public function getFilterFields(): array
    {
        return [
            Builder::text('find_value')
                ->required()
                ->label(__p('core::phrase.email'))
                ->description(__p('ban::phrase.use_the_asterisk_for_wildcard_entries'))
                ->yup(
                    Yup::string()
                        ->nullable()
                        ->required()
                ),
        ];
    }
}
