<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Console;

/**
 * Class Stub.
 */
class Stub
{
    /**
     * The stub path.
     *
     * @var string
     */
    protected $path;

    /**
     * The base path of stub file.
     *
     * @var null|string
     */
    protected static $basePath = null;

    /**
     * The replacements array.
     *
     * @var array<string, mixed>
     */
    protected $replaces = [];

    /**
     * The constructor.
     *
     * @param string               $path
     * @param array<string, mixed> $replaces
     */
    public function __construct(string $path, array $replaces = [])
    {
        $this->path     = $path;
        $this->replaces = $replaces;
    }

    /**
     * Create new self instance.
     *
     * @param string               $path
     * @param array<string, mixed> $replacements
     *
     * @return self
     */
    public static function create(string $path, array $replacements = [])
    {
        return new self($path, $replacements);
    }

    /**
     * Set stub path.
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get stub path.
     *
     * @return string
     */
    public function getPath(): string
    {
        $path = static::getBasePath() . $this->path;

        return file_exists($path) ? $path : app_path('Console/Commands/stubs/') . $this->path;
    }

    /**
     * Set base path.
     *
     * @param string $path
     */
    public static function setBasePath(string $path): void
    {
        static::$basePath = $path;
    }

    /**
     * Get base path.
     *
     * @return string|null
     */
    public static function getBasePath(): ?string
    {
        return static::$basePath;
    }

    /**
     * Get stub contents.
     *
     * @return string
     */
    public function getContents(): string
    {
        $contents = mf_get_contents($this->getPath());

        if ($contents === false) {
            abort(500, 'Cannot file_get_contents: ' . $this->getPath());
        }

        foreach ($this->replaces as $search => $replace) {
            $contents = str_replace('$' . strtoupper($search) . '$', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Get stub contents.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->getContents();
    }

    /**
     * Save stub to specific path.
     *
     * @param string $path
     * @param string $filename
     *
     * @return bool
     */
    public function saveTo(string $path, string $filename)
    {
        return file_put_contents($path . '/' . $filename, $this->getContents()) ? true : false;
    }

    /**
     * Set replacements array.
     *
     * @param array<string, mixed> $replaces
     *
     * @return self
     */
    public function replace(array $replaces = []): self
    {
        $this->replaces = $replaces;

        return $this;
    }

    /**
     * Get replacements.
     *
     * @return array<string, mixed>
     */
    public function getReplaces(): array
    {
        return $this->replaces;
    }

    /**
     * Handle magic method __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
