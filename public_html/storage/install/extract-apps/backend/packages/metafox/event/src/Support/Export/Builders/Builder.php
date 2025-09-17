<?php

namespace MetaFox\Event\Support\Export\Builders;

use MetaFox\Event\Support\Export\Payload;

class Builder
{
    public function __construct(protected Payload $payload) { }

    public function build(): string
    {
        $lines = [];

        foreach ($this->buildComponent() as $line) {
            $lines = array_merge($lines, $this->chipLine($line));
        }

        return implode("\r\n", $lines);
    }

    public function buildComponent(): array
    {
        $lines[] = "BEGIN:{$this->payload->getType()}";

        $lines = array_merge(
            $lines,
            $this->buildProperties(),
            $this->buildSubComponents()
        );

        $lines[] = "END:{$this->payload->getType()}";

        return $lines;
    }

    private function buildProperties(): array
    {
        $lines = [];

        foreach ($this->payload->getProperties() as $property) {

            $builder = new PropertyBuilder($property);

            $lines = array_merge(
                $lines,
                $builder->build()
            );
        }

        return $lines;
    }

    private function buildSubComponents(): array
    {
        $lines = [];

        foreach ($this->payload->getSubComponents() as $component) {
            $builder = new Builder($component->resolvePayload());

            $lines = array_merge(
                $lines,
                $builder->buildComponent()
            );
        }

        return $lines;
    }

    private function chipLine(string $line): array
    {
        $chippedLines = [];

        while (strlen($line) > 0) {
            if (strlen($line) > 75) {
                $chippedLines[] = mb_strcut($line, 0, 75, 'utf-8');
                $line           = ' ' . mb_strcut($line, 75, strlen($line), 'utf-8');
            } else {
                $chippedLines[] = $line;

                break;
            }
        }

        return $chippedLines;
    }
}
