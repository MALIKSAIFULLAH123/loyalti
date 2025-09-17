<?php

namespace MetaFox\Platform\Support\Module;

/**
 * Class GeneratorPath
 * @package MetaFox\Platform\Support\Module
 */
class GeneratorPath
{
    /** @var string */
    private $path;

    /** @var bool */
    private $generate;

    /** @var string */
    private $namespace;

    /**
     * GeneratorPath constructor.
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->path = $config['path'];
        $this->generate = $config['generate'] ?? false;
        $this->namespace = $config['namespace'] ?? $this->convertPathToNamespace($config['path']);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function generate(): bool
    {
        return $this->generate;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    private function convertPathToNamespace(string $path): string
    {
        return str_replace('/', '\\', $path);
    }
}
