<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Console;

use Illuminate\Filesystem\Filesystem;

/**
 * Class FileGenerator.
 */
class FileGenerator
{
    /**
     * The path wil be used.
     *
     * @var string
     */
    protected string $path;

    /**
     * The contents will be used.
     *
     * @var string
     */
    protected string $contents;

    /**
     * The laravel filesystem or null.
     *
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var bool
     */
    private bool $overwriteFile = false;

    /**
     * The constructor.
     *
     * @param string          $path
     * @param string          $contents
     * @param Filesystem|null $filesystem
     */
    public function __construct(
        string $path,
        string $contents,
        Filesystem $filesystem = null
    ) {
        $this->path = $path;
        $this->contents = $contents;
        $this->filesystem = $filesystem === null ? new Filesystem() : $filesystem;
    }

    /**
     * Get contents.
     *
     * @return string
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * Set contents.
     *
     * @param string $contents
     *
     * @return $this
     */
    public function setContents(string $contents)
    {
        $this->contents = $contents;

        return $this;
    }

    /**
     * Get filesystem.
     *
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Set filesystem.
     *
     * @param Filesystem $filesystem
     *
     * @return $this
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set path.
     *
     * @param mixed $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function withFileOverwrite(bool $overwrite): self
    {
        $this->overwriteFile = $overwrite;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOverwriteFile(): bool
    {
        return $this->overwriteFile;
    }

    /**
     * Generate the file.
     *
     * @return bool|int
     * @throws FileAlreadyExistException
     */
    public function generate()
    {
        if (!$this->filesystem->exists($this->getPath())) {
            return $this->filesystem->put($this->getPath(), $this->getContents());
        }
        if ($this->isOverwriteFile() === true) {
            return $this->filesystem->put($this->getPath(), $this->getContents());
        }

        throw new FileAlreadyExistException('File already exists!');
    }
}
