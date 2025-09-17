<?php

namespace MetaFox\EMoney\Providers\CurrencyConversionRate;

use MetaFox\EMoney\Contracts\CurrencyConverterInterface;
use MetaFox\EMoney\Repositories\CurrencyConverterRepositoryInterface;

abstract class AbstractConversionProvider implements CurrencyConverterInterface
{
    /**
     * @var array
     */
    protected array $config;

    /**
     * @var array|null
     */
    protected ?array $payload = null;

    /**
     * @var array|null
     */
    protected ?array $response = null;

    /**
     * @var CurrencyConverterRepositoryInterface
     */
    protected CurrencyConverterRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = $this->getConverterRepository();

        $this->config = $this->repository->getConfig($this->getServiceName());
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function getInformationLink(): ?string
    {
        return null;
    }

    protected function getConverterRepository(): CurrencyConverterRepositoryInterface
    {
        return resolve(CurrencyConverterRepositoryInterface::class);
    }

    protected function resetData(): void
    {
        $this->payload  = null;
        $this->response = null;
    }
}
