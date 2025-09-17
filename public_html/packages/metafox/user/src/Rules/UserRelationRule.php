<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;

/**
 * Class UserRelationRule.
 */
class UserRelationRule implements Rule, DataAwareRule
{
    /**
     * @var array
     */
    protected array $data = [];
    protected string $attribute;

    /**
     * Set the data under validation.
     *
     * @param  array $data
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;
        $params          = $this->data;
        $phrase          = toTranslationKey($params['package_id'], $params['group'], $value);

        return !resolve(PhraseRepositoryInterface::class)->checkExistKey($phrase, $params['locale']);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('group::validation.the_value_must_be_unique', [
            'value' => str_replace('_', ' ', $this->attribute),
        ]);
    }
}
