<?php

namespace Zenstruck\Document\Library;

use Zenstruck\Document;
use Zenstruck\Document\Library;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LazyLibrary implements Library
{
    /** @var callable():Library|Library|null */
    private $library;

    /**
     * @param callable():Library|Library|null $library
     */
    public function __construct(callable|Library|null $library = null)
    {
        $this->library = $library;
    }

    public function setLibrary(Library $library): static
    {
        $this->library = $library;

        return $this;
    }

    public function open(string $path): Document
    {
        return $this->library()->open($path);
    }

    public function has(string $path): bool
    {
        return $this->library()->has($path);
    }

    public function store(string $path, \SplFileInfo|Document $document, array $config = []): static
    {
        $this->library()->store($path, $document, $config);

        return $this;
    }

    public function delete(string $path): static
    {
        $this->library()->delete($path);

        return $this;
    }

    private function library(): Library
    {
        if ($this->library instanceof Library) {
            return $this->library;
        }

        if (\is_callable($this->library)) {
            return $this->library = ($this->library)();
        }

        throw new \LogicException(); // todo
    }
}
