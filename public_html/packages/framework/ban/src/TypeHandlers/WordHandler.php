<?php

namespace MetaFox\Ban\TypeHandlers;

use MetaFox\Ban\Rules\UniqueBanRuleRule;
use MetaFox\Ban\Supports\Constants;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

class WordHandler extends AbstractTypeHandler
{
    public function getType(): string
    {
        return Constants::BAN_WORD_TYPE;
    }

    public function getValidationRules(): array
    {
        return [
            'find_value'  => ['required', 'string', new UniqueBanRuleRule()],
            'replacement' => ['required', 'string'],
        ];
    }

    public function getFormTitle(): string
    {
        return __p('ban::phrase.add_new_word');
    }

    public function isSupportBanUser(): bool
    {
        // This scope of task (MFOXCORE-8624): Output that contains banned words should be sanitized.
        // So we temporarily hide the ban user feature in Add New Word.

        return false;
    }

    public function getFilterFields(): array
    {
        return [
            Builder::text('find_value')
                ->required()
                ->label(__p('ban::phrase.word'))
                ->description(__p('ban::phrase.use_the_asterisk_for_wildcard_entries'))
                ->yup(
                    Yup::string()
                        ->nullable()
                        ->required()
                ),
            Builder::text('replacement')
                ->required()
                ->label(__p('ban::phrase.replacement'))
                ->yup(
                    Yup::string()
                        ->nullable()
                        ->required()
                ),
        ];
    }
}
