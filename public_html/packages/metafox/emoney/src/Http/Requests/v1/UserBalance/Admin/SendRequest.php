<?php
namespace MetaFox\EMoney\Http\Requests\v1\UserBalance\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\EMoney\Facades\UserBalance;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Rules\ValidateUserForBalanceAdjustmentRule;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class SendRequest extends FormRequest
{
    /**
     * @return array[]
     * @throws AuthorizationException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function rules(): array
    {
        return array_merge([
            'currency' => ['required', new AllowInRule(array_column(app('currency')->getActiveOptions(), 'value'))],
            'user_id'  => ['required', 'integer', resolve(ValidateUserForBalanceAdjustmentRule::class)],
        ], $this->getPriceRule());
    }

    protected function getPriceRule(): array
    {
        $currency = request()->get('currency');

        $userId = request()->get('user_id');

        if (!$currency || !$userId) {
            return [];
        }

        $user = resolve(UserRepositoryInterface::class)->find($userId);

        $currentBalance = resolve(StatisticRepositoryInterface::class)->getUserBalance($user, $currency);

        $min = $this->getMinAmount($currentBalance);

        $max = $this->getMaxAmount($currentBalance);

        if (0 == $max || 0 == $min) {
            throw new AuthorizationException();
        }

        return [
            'price_' . $currency => ['required', 'numeric', 'min:' . $min, 'max:' . $max],
        ];
    }

    protected function getMinAmount(float $currentBalance): float
    {
        return UserBalance::getMinValueForSending($currentBalance);
    }

    protected function getMaxAmount(float $currentBalance): float
    {
        return UserBalance::getMaxValueForSending($currentBalance);
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $currency = request()->get('currency');

        Arr::set($data, 'price', round(Arr::pull($data, 'price_' . $currency), 2));

        return $data;
    }
}
