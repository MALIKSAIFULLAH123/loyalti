<?php

namespace MetaFox\Featured\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Repositories\ItemRepositoryInterface;

class PackageRule implements ValidationRule, DataAwareRule
{
    /**
     * @var array
     */
    protected array $data = [];

    protected ItemRepositoryInterface $itemRepository;

    public function __construct()
    {
        $this->itemRepository = resolve(ItemRepositoryInterface::class);
    }

    /**
     * Set the data under validation.
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string  $attribute
     * @param mixed   $value
     * @param Closure $fail
     * @return void
     *
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $item = Feature::morphItemFromEntityType(
                Arr::get($this->data, 'item_type'),
                Arr::get($this->data, 'item_id')
            );

            $package = $this->itemRepository->getPackage($value);

            $this->itemRepository->validatePackage($item, $package);
        } catch (\Throwable $e) {
            $fail(__p('featured::validation.package_not_found'));
        }
    }
}
