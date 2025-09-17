<?php

namespace MetaFox\User\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use MetaFox\User\Models\UserGender;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;

/**
 * Class CustomGenderRule.
 */
class CustomGenderRule implements Rule, DataAwareRule
{
    /**
     * @var array
     */
    protected array $data = [];

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
        return $this->checkExistGender($this->data);
    }

    protected function checkExistGender(array $params)
    {
        $phrase = toTranslationKey($params['package_id'], $params['group'], $params['name']);

        $gender = resolve(UserGenderRepositoryInterface::class)->findGenderByPhrase($phrase);

        return !$gender instanceof UserGender;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('user::validation.the_gender_must_be_unique');
    }
}
