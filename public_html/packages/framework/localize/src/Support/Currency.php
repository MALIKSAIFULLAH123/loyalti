<?php

namespace MetaFox\Localize\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MetaFox\Core\Support\CacheManager;
use MetaFox\Localize\Contracts\CurrencySupportContract;
use MetaFox\Localize\Models\Currency as Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class Currency.
 */
class Currency implements CurrencySupportContract
{
    /**
     * @var array<string, Model>
     */
    private array $currencies;

    public const PREG_FORMATER = '/#([^#]*)([#]*)([^0{\s]*)([0]*)/u';

    public function __construct()
    {
        $this->init();
    }

    public function getAllActiveCurrencies(): array
    {
        return Arr::where($this->currencies, function (Model $value) {
            return $value->is_active;
        });
    }

    public function getCacheName(): string
    {
        return CacheManager::CORE_CURRENCY_CACHE;
    }

    public function clearCache(): void
    {
        Cache::forget($this->getCacheName());
    }

    /**
     * @return array<string, Model>
     */
    public function getCurrencies(): array
    {
        return $this->currencies;
    }

    /**
     * @return array<int, array>
     */
    public function getActiveOptions()
    {
        return Cache::rememberForever(
            __METHOD__,
            function () {
                return array_map(function (Model $model) {
                    return [
                        'label'  => $model->name, 'value' => $model->code,
                        'symbol' => html_entity_decode($model->symbol),
                    ];
                }, Model::query()
                    ->where('is_active', '=', 1)
                    ->orderBy('ordering')
                    ->get()
                    ->all());
            }
        );
    }

    /**
     * @param string|null $code
     *
     * @return mixed|string|null
     */
    public function getName(?string $code)
    {
        if (!$code) {
            return null;
        }

        return Cache::rememberForever(__METHOD__ . $code, function () use ($code) {
            /** @var ?Model $model */
            $model = Model::query()->where('code', '=', $code)->first();

            return $model?->name;
        });
    }

    protected function init(): void
    {
        $this->currencies = Cache::rememberForever(
            __METHOD__,
            function () {
                return Model::query()
                    ->orderBy('ordering')
                    ->orderBy('name')
                    ->get()
                    ->keyBy('code')
                    ->all();
            }
        );
    }

    public function getUserCurrencyId(User $context): string
    {
        $currency = $this->getDefaultCurrencyId();

        if ($context->entityId() > 0) {
            $profile = $context->profile;
            if (null !== $profile && null !== $profile->currency_id) {
                $currency = $profile->currency_id;
            }
        }

        return $currency;
    }

    public function getDefaultCurrencyId(): string
    {
        $currencies = $this->getAllActiveCurrencies();

        $code = MetaFoxConstant::DEFAULT_CURRENCY_ID;

        foreach ($currencies as $currency) {
            if ($currency->is_default) {
                $code = $currency->code;
                break;
            }
        }

        return $code;
    }

    public function getPriceFormatByCurrencyId(string $currencyId, float $price, ?string $precision = null): ?string
    {
        if (!Arr::has($this->currencies, $currencyId)) {
            return sprintf('%s %s', number_format($price, 2), $currencyId);
        }

        $params = $this->getFormatForPrice($currencyId, $precision);

        $form = Arr::get($params, 'pattern');

        $decimalPoint = Arr::get($params, 'decimal_separator');

        $thousandSeparator = Arr::get($params, 'thousand_separator');

        $precision = Arr::get($params, 'precision');

        $currency = Arr::get($this->currencies, $currencyId);

        $symbol            = html_entity_decode($currency->symbol);

        return strtr($form, [
            '{0}' => $symbol,
            '{1}' => $currencyId,
            '{3}' => number_format($price, $precision, $decimalPoint, $thousandSeparator),
        ]);
    }

    public function getFormatForPrice(string $currencyId, ?string $precision = null, bool $replaceForCurrency = false): ?array
    {
        if (!Arr::has($this->currencies, $currencyId)) {
            return null;
        }

        $currency = Arr::get($this->currencies, $currencyId);

        $pattern           = $currency->format;
        $form              = '{0} {3}';
        $decimalPoint      = '.';
        $thousandSeparator = ',';

        if (preg_match(self::PREG_FORMATER, $pattern, $result)) {
            $decimalPoint = $result[3];
            if (null === $precision) {
                $precision = strlen($result[4]);
            }
            $thousandSeparator = $result[1];
            $form              = str_replace($result[0], '{3}', $pattern);
        }

        if ($replaceForCurrency) {
            $symbol            = html_entity_decode($currency->symbol);

            $form = strtr($form, [
                '{0}' => $symbol,
                '{1}' => $currencyId,
            ]);
        }

        return [
            'pattern'            => $form,
            'decimal_separator'  => $decimalPoint,
            'thousand_separator' => $thousandSeparator,
            'precision'          => $precision,
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules(string $name, array $rules = []): array
    {
        $codes = collect($this->getAllActiveCurrencies())->keys()->values()->toArray();

        $result = [];

        foreach ($codes as $code) {
            $result["$name.$code"] = $rules;
        }

        return $result;
    }

    public function getCurrencyOptions(): array
    {
        return collect($this->getCurrencies())
            ->map(function (Model $currency) {
                return [
                    'label'  => $currency->name,
                    'value' => $currency->code,
                    'symbol' => html_entity_decode($currency->symbol),
                ];
            })
            ->values()
            ->toArray();
    }
}
