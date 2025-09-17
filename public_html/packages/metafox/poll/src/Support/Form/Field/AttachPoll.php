<?php

namespace MetaFox\Poll\Support\Form\Field;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;

class AttachPoll extends AbstractField
{
    public const COMPONENT_NAME = 'AttachPoll';

    /**
     * @var array
     */
    private array $endpointParams = [];

    /**
     * @var string
     */
    private string $endpoint = 'poll/integration-form';

    public function initialize(): void
    {
        $ownerId = request()->get('owner_id', null);

        $params = [];

        if ($ownerId) {
            Arr::set($params, 'owner_id', $ownerId);
        }

        if (count($params)) {
            $this->setEndpointQueryParams($params);
        }

        $this->name('integrated_item')
            ->component(self::COMPONENT_NAME)
            ->fullWidth()
            ->placeholder(__p('poll::phrase.attach_poll'))
            ->variant('outlined')
            ->setFormUrl();
    }

    /**
     * @param string $endpoint
     * @return $this
     */
    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setEndpointQueryParams(array $params = []): self
    {
        $this->endpointParams = array_merge($this->endpointParams, $params);

        return $this;
    }

    public function getEndpointQueryParams(): array
    {
        return $this->endpointParams;
    }

    public function getFormUrl(): string
    {
        $endpoint = url_utility()->makeApiUrl($this->getEndpoint());

        $queryParams = $this->getEndpointQueryParams();

        if (count($queryParams)) {
            $endpoint .= '?' . http_build_query($queryParams);
        }

        return $endpoint;
    }

    public function setFormUrl(): self
    {
        return $this->setAttribute('formUrl', $this->getFormUrl());
    }
}
