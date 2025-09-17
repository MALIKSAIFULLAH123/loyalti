<?php

namespace MetaFox\Form;

/**
 * @mixin AbstractForm
 */
trait MultiStepFormTrait
{
    /**
     * @param  array<string, mixed> $meta
     * @return $this
     */
    public function meta(array $meta = []): self
    {
        $currentData = $this->getAttribute('meta') ?? [];

        return $this->setAttribute('meta', array_merge($currentData, $meta));
    }

    public function type(string $type): self
    {
        return $this->meta(['type' => $type]);
    }
}
