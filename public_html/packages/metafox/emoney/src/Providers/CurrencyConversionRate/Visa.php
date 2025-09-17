<?php

namespace MetaFox\EMoney\Providers\CurrencyConversionRate;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\EMoney\Support\Support;

class Visa extends AbstractConversionProvider
{
    public const ENDPOINT_RESOURCE_PATH = 'v2/foreignexchangerates';
    private array $endpoints            = [
        Support::TEST_MODE => 'https://sandbox.api.visa.com/forexrates/v2/foreignexchangerates',
        Support::LIVE_MODE => 'https://api.visa.com/forexrates/v2/foreignexchangerates',
    ];
    public function getExchangeRate(string $src, string $dest): ?float
    {
        if (!$this->isAvailable()) {
            return null;
        }

        if ($src == $dest) {
            return null;
        }

        $this->resetData();

        return $this->processExchangeRate($src, $dest);
    }

    private function processExchangeRate(string $src, string $dest): ?float
    {
        try {
            $url = $this->getEndpointUrl();

            if (null === $url) {
                return null;
            }

            $apiKey        = $this->getApiKey();
            $sharedSecret  = $this->getSharedSecret();
            $resourcePath  = self::ENDPOINT_RESOURCE_PATH;
            $queryString   = 'apiKey=' . $apiKey;
            $this->payload = [
                'initiatingPartyId'       => 1002,
                'rateProductCode'         => 'BANK',
                'destinationCurrencyCode' => $dest,
                'sourceCurrencyCode'      => $src,
                'quoteIdRequired'         => true,
            ];
            $payloadJson      = json_encode($this->payload);

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payloadJson);

            curl_setopt($curl, CURLOPT_PORT, 443);
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 2);
            curl_setopt($curl, CURLOPT_SSLVERSION, 1);

            $time = time();

            $preHashString = $time . $resourcePath . $queryString . $payloadJson;

            $token = 'xv2:' . $time . ':' . hash_hmac('sha256', $preHashString, $sharedSecret);

            $authHeader = 'x-pay-token: ' . $token;

            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                $authHeader,
            ]);

            $url = $url . '?' . $queryString;

            curl_setopt($curl, CURLOPT_URL, $url);

            $response = curl_exec($curl);

            $responseInfo = curl_getinfo($curl);

            $statusCode = Arr::get($responseInfo, 'http_code', 500);

            if ($statusCode < 200 || $statusCode > 299) {
                return null;
            }

            if (is_string($response)) {
                $response = json_decode($response, true);
            }

            if (!is_array($response)) {
                return null;
            }

            $this->response = $response;

            $rate = Arr::get($response, 'conversionRate');

            if (!is_numeric($rate)) {
                return null;
            }

            return round((float) $rate, Support::MAXIMUM_EXCHANGE_RATE_DECIMAL_PLACE_NUMBER);
        } catch (\Throwable $throwable) {
            Log::error('exchange rate error: ' . $throwable->getMessage());
        }

        return null;
    }

    private function getEndpointUrl(): ?string
    {
        $mode = Arr::get($this->config, 'mode', Support::TEST_MODE);

        return Arr::get($this->endpoints, $mode);
    }

    public function isAvailable(): bool
    {
        if (!$this->getApiKey() || !$this->getSharedSecret()) {
            return false;
        }

        return true;
    }

    public function getServiceName(): string
    {
        return Support::SERVICE_VISA;
    }

    public function getInformationLink(): ?string
    {
        return 'https://developer.visa.com/capabilities/foreign_exchange';
    }

    public function getTitle(): string
    {
        return 'Visa';
    }

    /**
     * @return string|null
     */
    private function getApiKey(): ?string
    {
        return Arr::get($this->config, 'api_key');
    }

    /**
     * @return string|null
     */
    private function getSharedSecret(): ?string
    {
        return Arr::get($this->config, 'shared_secret');
    }
}
