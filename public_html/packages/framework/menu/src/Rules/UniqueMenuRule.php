<?php

namespace MetaFox\Menu\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;

/**
 * Class UniqueMenuRule.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UniqueMenuRule implements Rule, DataAwareRule
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
    public function __construct()
    {
    }

    public function passes($attribute, $value): bool
    {
        $params          = $this->data;

        /** @var MenuRepositoryInterface $service */
        $service = resolve(MenuRepositoryInterface::class);

        return !$service->isExists($params['name'], $params['resolution']);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('menu::phrase.the_menu_with_that_name_already_exists');
    }
}
