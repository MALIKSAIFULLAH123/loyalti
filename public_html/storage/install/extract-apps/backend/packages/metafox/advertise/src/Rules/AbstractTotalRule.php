<?php

namespace MetaFox\Advertise\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Repositories\PlacementRepositoryInterface;
use MetaFox\Advertise\Support\Support;

abstract class AbstractTotalRule implements
    Rule,
    ValidatorAwareRule,
    DataAwareRule
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var ValidatorContract|null
     */
    protected ?ValidatorContract $validator = null;

    /**
     * @var string|null
     */
    protected ?string $message = null;

    public function __construct(protected bool $isAdminCP = false) {}

    /**
     * @return string
     */
    abstract public function ruleType(): string;

    /**
     * @param int $min
     * @return string
     */
    abstract public function typeErrorMessage(int $min): string;

    /**
     * @param int $max
     * @return string
     */
    abstract public function maxNumberErrorMessage(int $max): string;

    public function passes($attribute, $value)
    {
        $placement = resolve(PlacementRepositoryInterface::class)->find(Arr::get($this->data, 'placement_id'));

        $type = $this->ruleType();

        if (!$type) {
            return false;
        }

        if ($placement->placement_type !== $type) {
            return true;
        }

        $min = match ($type) {
            Support::PLACEMENT_CPM => 100,
            default                => 1,
        };

        if ($this->isAdminCP) {
            $min = 0;
        }

        $typeErrorMessage = $this->typeErrorMessage($min);

        if (!$this->validator->validateNumeric($attribute, $value)) {
            $this->message = $typeErrorMessage;

            return false;
        }

        $value = (int) $value;

        if ($value < $min) {
            $this->message = $typeErrorMessage;

            return false;
        }

        if ($value > Support::MAX_TOTAL_NUMBER) {
            $this->message = $this->maxNumberErrorMessage(Support::MAX_TOTAL_NUMBER);

            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->message;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    public function docs(): array
    {
        return [
            'type'   => 'integer',
            'setter' => (fn () => rand(1, 99)),
        ];
    }
}
